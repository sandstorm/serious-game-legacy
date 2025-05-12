<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\InitPlayerGuthaben;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GuthabenCalculator;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Command\StartNewYear;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;
use Domain\CoreGameLogic\Feature\Jahreswechsel\State\LeitzinsAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});

test('Event stream can be accessed', function () {
    $stream = GameEvents::fromArray([
        new NewYearWasStarted(
            newYear: new CurrentYear(1),
            leitzins: new Leitzins(3)
        ),
        new NewYearWasStarted(
            newYear: new CurrentYear(1),
            leitzins: new Leitzins(5)
        )
    ]);

    expect(LeitzinsAccessor::forStream($stream)->value)->toBe(5);
});

test('Current Player Handling', function () {
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            PlayerId::fromString('p1'),
            PlayerId::fromString('p2'),
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new StartNewYear(
        newYear: new CurrentYear(1),
        leitzins: new Leitzins(3)
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
            lebensziel: new Lebensziel('Lebensziel XYZ'),
            playerId: PlayerId::fromString('p1'),
        ),
        new LebenszielChosen(
            lebensziel: new Lebensziel('Lebensziel ABC'),
            playerId: PlayerId::fromString('p2'),
        ),
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p1'))->lebensziel->value)->toBe('Lebensziel XYZ');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p2'))->lebensziel->value)->toBe('Lebensziel ABC');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(PlayerId::fromString('p3')))->toBe(null);
});


test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new StartNewYear(
        newYear: new CurrentYear(1),
        leitzins: new Leitzins(3)
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($p1, new CardId("segellehrer")));
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($p1, new CardId("sozialarbeiterIn"), new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($p1));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->value)->toBe("MODIFIER:ausetzen");
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty();
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC
});


test('wie viel Guthaben hat Player zur Verfügung', function () {
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($p1, $p2));
    $this->coreGameLogic->handle($this->gameId, new InitPlayerGuthaben(new Guthaben(50000)));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GuthabenCalculator::forStream($stream)->forPlayer($p1)->value)->toBe(50000);
});
