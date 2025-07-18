<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Tests\TestCase;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
});

test('PreGameLogic normal flow', function () {
    /** @var TestCase $this */
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
        lebenszielId: LebenszielId::create(1)
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebenszielId: LebenszielId::create(2),
    ));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    $expectedLebenszielForP1 = LebenszielFinder::findLebenszielById(LebenszielId::create(1));
    $expectedLebenszielForP2 = LebenszielFinder::findLebenszielById(LebenszielId::create(2));

    expect(PreGameState::isReadyForGame($gameEvents))->toBeTrue()
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p1->value]->lebensziel->name)->toEqual($expectedLebenszielForP1->name)
        ->and(PreGameState::playersWithNameAndLebensziel($gameEvents)[$this->p2->value]->lebensziel->name)->toEqual($expectedLebenszielForP2->name)
        ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->p1))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE));
});

test('test lebensziel kompetenzen', function() {
    $this->setupBasicGame();

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // player 1
    // bildung
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->definition->bildungsKompetenzSlots)->toBe(2);
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->definition->freizeitKompetenzSlots)->toBe(1);
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

    // player 2
    // bildung
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->definition->bildungsKompetenzSlots)->toBe(1);
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    // freizeit
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->definition->freizeitKompetenzSlots)->toBe(3);
    expect(PlayerState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineFreizeit)->toBe(0);

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
