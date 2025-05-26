<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\GameId;

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

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Spielerwechsel
    $this->coreGameLogic->handle($this->gameId, new EndSpielzug(
        player: PlayerId::fromString('p1'),
    ));
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');

    // Spielerwechsel mit wieder vorn beginnen
    $this->coreGameLogic->handle($this->gameId, new EndSpielzug(
        player: PlayerId::fromString('p2'),
    ));
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    /* TODO: some problem here??
    // Player pausieren / ersetzen.
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            PlayerId::fromString('p1'),
            PlayerId::fromString('p3'),
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new EndSpielzug(
        player: PlayerId::fromString('p1'),
    ));
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p3');
    */
});
