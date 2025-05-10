<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGamePhase;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = new GameId('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
});

test('PreGameLogic normal flow', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGamePhase::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse();
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1]->name)->toEqual(null);
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse();
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1]->name)->toEqual('Player 1');
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse();
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1]->name)->toEqual('Player 1a');
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2]->name)->toEqual(null);

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(PreGameState::isReadyForGame($gameStream))->toBeFalse();
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1]->name)->toEqual('Player 1a');
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2]->name)->toEqual('Player 2');

    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new Lebensziel('Lebensziel XYZ'),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new Lebensziel('Lebensziel AAA'),
    ));
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PreGameState::isReadyForGame($gameStream))->toBeTrue();
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p1]->lebensziel->value)->toEqual('Lebensziel AAA');
    expect(PreGameState::playersWithNameAndLebensziel($gameStream)[$this->p2]->lebensziel->value)->toEqual('Lebensziel XYZ');
});

test('PreGameLogic can only start once', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGamePhase::create(
        numberOfPlayers: 2,
    ));

    $this->coreGameLogic->handle($this->gameId, StartPreGamePhase::create(
        numberOfPlayers: 2,
    ));
})->throws(RuntimeException::class);

test('SetNameForPlayer throws if unknown PlayerId', function () {
    $this->coreGameLogic->handle($this->gameId, StartPreGamePhase::create(
        numberOfPlayers: 2,
    ));

    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(playerId: PlayerId::fromString('UNKNOWN'), name: 'Player FOO'));
})->throws(RuntimeException::class);
