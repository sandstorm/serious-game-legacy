<?php

declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\GameEventStore;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Neos\EventStore\Helper\InMemoryEventStore;
use Neos\EventStore\Model\EventStream\ExpectedVersion;

beforeEach(function () {
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
});

describe('definitions version', function () {
    it('stores the current definitions version for new games', function () {
        $coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
        $coreGameLogic->handle($this->gameId, StartPreGame::create(numberOfPlayers: 2)
            ->withFixedPlayerIdsForTesting($this->p1, $this->p2));
        $coreGameLogic->handle($this->gameId, new SetNameForPlayer(playerId: $this->p1, name: 'Player 1'));

        $gameEvents = $coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findFirst(PreGameStarted::class)->definitionsVersion)->toBe(Configuration::DEFINITIONS_VERSION)
            ->and(PreGameState::isPlayableWithCurrentDefinitions($gameEvents))->toBeTrue()
            ->and(PreGameState::hasPlayerName($gameEvents, $this->p1))->toBeTrue();
    });

    it('rejects commands for games created on a different version of the game', function () {
        $gameEventStore = new GameEventStore(new InMemoryEventStore());
        $gameEventStore->commit($this->gameId, GameEventsToPersist::with(new PreGameStarted(
            playerIds: [$this->p1, $this->p2],
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(Configuration::STARTKAPITAL_VALUE)),
            definitionsVersion: Configuration::DEFINITIONS_VERSION - 1,
        )), ExpectedVersion::NO_STREAM());
        $coreGameLogic = new CoreGameLogicApp($gameEventStore);

        // the game can still be loaded (e.g. to view or export it)
        expect(PreGameState::isPlayableWithCurrentDefinitions($coreGameLogic->getGameEvents($this->gameId)))->toBeFalse();

        $coreGameLogic->handle($this->gameId, new SetNameForPlayer(playerId: $this->p1, name: 'Player 1'));
    })->throws(
        RuntimeException::class,
        'Game game1 was created on a different version of the game and cannot be continued',
        1790760000
    );

    it('uses version 0 for games created before the definitions version was introduced', function () {
        $preGameStarted = PreGameStarted::fromArray([
            'playerIds' => ['p1', 'p2'],
            'resourceChanges' => (new ResourceChanges())->jsonSerialize(),
        ]);

        expect($preGameStarted->definitionsVersion)->toBe(0);
    });
});
