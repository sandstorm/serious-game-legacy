<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\Definitions\Lebensziel\Model\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Model\LebenszielKompetenzbereichDefinition;
use Domain\Definitions\Lebensziel\Model\LebenszielPhaseDefinition;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
});

test('PreGameLogic normal flow', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual(null)
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual('Player 2');

    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new LebenszielDefinition(
            value: 'Lebensziel XYZ',
            phases: [
                new LebenszielPhaseDefinition(
                    bildungsKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::BILDUNG,
                        slots: 2,
                    ),
                    freizeitKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::FREIZEIT,
                        slots: 1,
                    ),
                ),
            ],
        ),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new LebenszielDefinition(
            value: 'Lebensziel AAA',
            phases: [
                new LebenszielPhaseDefinition(
                    bildungsKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::BILDUNG,
                        slots: 2,
                    ),
                    freizeitKompetenz: new LebenszielKompetenzbereichDefinition(
                        name: KompetenzbereichEnum::FREIZEIT,
                        slots: 1,
                    ),
                ),
            ],
        ),
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PreGameState::isReadyForGame($gameStream))->toBeTrue()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->lebensziel->value)->toEqual('Lebensziel AAA')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->lebensziel->value)->toEqual('Lebensziel XYZ');
});

test('PreGameLogic can only start once', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    ));

    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    ));
})->throws(RuntimeException::class);

test('SetNameForPlayer throws if unknown PlayerId', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    ));

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(playerId: PlayerId::fromString('UNKNOWN'), name: 'Player FOO'));
})->throws(RuntimeException::class);
