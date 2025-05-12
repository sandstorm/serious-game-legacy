<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\Definitions\Lebensziel\Model\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Model\LebenszielPhaseDefinition;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});

test('Current Player Handling', function () {
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            PlayerId::fromString('p1'),
            PlayerId::fromString('p2'),
        ]
    ));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Spielerwechsel
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: PlayerId::fromString('p1'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');

    // Spielerwechsel mit wieder vorn beginnen
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: PlayerId::fromString('p2'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    /* TODO: some problem here??
    // Player pausieren / ersetzen.
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            PlayerId::fromString('p1'),
            PlayerId::fromString('p3'),
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: PlayerId::fromString('p1'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p3');
    */
});

test('Init Lebensziel', function () {
    $stream = GameEvents::fromArray([
        new GameWasStarted(
            playerOrdering: [
                PlayerId::fromString('p1'),
                PlayerId::fromString('p2'),
            ]
        ),
        new LebenszielChosen(
            playerId: PlayerId::fromString('p1'),
            lebensziel: new LebenszielDefinition(
                id: new LebenszielId('Lebensziel XYZ'),
                phaseDefinitions: [
                    new LebenszielPhaseDefinition(
                        bildungsKompetenzSlots:2,
                        freizeitKompetenzSlots:1,
                    ),
                ],
            ),
        ),
        new LebenszielChosen(
            playerId: PlayerId::fromString('p2'),
            lebensziel: new LebenszielDefinition(
                id: new LebenszielId('Lebensziel ABC'),
                phaseDefinitions: [
                    new LebenszielPhaseDefinition(
                        bildungsKompetenzSlots:2,
                        freizeitKompetenzSlots:1,
                    ),
                ],
            ),
        ),
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p1'))->definition->id->value ?? null)->toBe('Lebensziel XYZ');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p2'))->definition->id->value ?? null)->toBe('Lebensziel ABC');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p3')))->toBe(null);
});
