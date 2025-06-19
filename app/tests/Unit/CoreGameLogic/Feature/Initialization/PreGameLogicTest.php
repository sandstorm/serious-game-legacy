<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectPlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColors;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;

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
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->name)->toEqual(null)
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1',
    ));
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->name)->toEqual('Player 1')
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->name)->toEqual('Player 2');

    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: LebenszielId::create(1)
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: LebenszielId::create(2),
    ));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    $expectedLebenszielForP1 = LebenszielFinder::findLebenszielById(LebenszielId::create(1));
    $expectedLebenszielForP2 = LebenszielFinder::findLebenszielById(LebenszielId::create(2));

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->lebensziel->name)->toEqual($expectedLebenszielForP1->name)
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->lebensziel->name)->toEqual($expectedLebenszielForP2->name);

    expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->p1))->toEqual(50000);

    $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
        playerId: $this->p1,
        playerColor: null, // color is chosen by the system
    ));

    $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
        playerId: $this->p2,
        playerColor: null, // color is chosen by the system
    ));
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeTrue();
    expect(PlayerState::getPlayerColor($gameEvents, $this->p1))->toBeIn(PlayerColors::asArray());

    // Check that the second player has a different color
    expect(PlayerState::getPlayerColor($gameEvents, $this->p2))->toBeIn(PlayerColors::asArray())
        ->and(PlayerState::getPlayerColor($gameEvents, $this->p1))->not->toEqual(PlayerState::getPlayerColor($gameEvents, $this->p2));
});

test('test lebensziel kompetenzen', function() {
    $this->setupBasicGame();

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // player 1
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    // player 2
    // bildung
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->definition->bildungsKompetenzSlots)->toBe(1);
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->definition->freizeitKompetenzSlots)->toBe(3);
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

});

test('set specific player color', function() {
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
        playerId: $this->p2,
        playerColor: new PlayerColor('#000000'),
    ));
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameEvents))->toBeFalse()
        ->and(PlayerState::getPlayerColor($gameEvents, $this->p2))->toEqual('#000000');
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
