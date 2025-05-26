<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\State\ZeitsteineState;
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
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual(null)
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1',
    ));
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->name)->toEqual('Player 1a')
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->name)->toEqual('Player 2');

    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: LebenszielId::create(1)
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: LebenszielId::create(2),
    ));

    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $expectedLebenszielForP1 = LebenszielFinder::findLebenszielById(LebenszielId::create(1));
    $expectedLebenszielForP2 = LebenszielFinder::findLebenszielById(LebenszielId::create(2));

    expect(PreGameState::isReadyForGame($gameStream))->toBeTrue()
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1->value]->lebensziel->name)->toEqual($expectedLebenszielForP1->name)
        ->and(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2->value]->lebensziel->name)->toEqual($expectedLebenszielForP2->name);

    expect(ZeitsteineState::forPlayer($gameStream, $this->p1)->value)->toBe(3);
    expect(PlayerState::getGuthabenForPlayer($gameStream, $this->p1))->toBe(50000);
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
