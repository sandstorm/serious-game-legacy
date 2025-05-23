<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\EventStore;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\Definitions\Lebensziel\LebenszielFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: new LebenszielId(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: new LebenszielId(2),
    ));
    $this->coreGameLogic->handle($this->gameId, new StartGame(
        playerOrdering: [$this->p1, $this->p2]
    ));
});

describe('find all after last of type', function () {
    it('finds all elements after last event of type', function () {
        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        $eventsAfterSelectedEvent = $stream->findAllAfterLastOfType(NameForPlayerWasSet::class);

        expect($eventsAfterSelectedEvent->count())->toBe(3);

        $events = iterator_to_array($eventsAfterSelectedEvent->getIterator());

        $expectedLebenszielForP1 = LebenszielFinder::findLebenszielById(1);
        $expectedLebenszielForP2 = LebenszielFinder::findLebenszielById(2);

        expect($events[0]::class)->toBe(LebenszielWasSelected::class)
            ->and($events[0]->lebensziel->name)->toBe($expectedLebenszielForP1->name)
            ->and($events[1]::class)->toBe(LebenszielWasSelected::class)
            ->and($events[1]->lebensziel->name)->toBe($expectedLebenszielForP2->name)
            ->and($events[2]::class)->toBe(GameWasStarted::class)
            ->and($events[2]->playerOrdering[0])->toBe($this->p1);
    });


    it('returns empty stream if no events are found', function () {
        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        expect($stream->findAllAfterLastOfType(GameWasStarted::class)->count())->toBe(0);
    });

    it('throws if no event of type is found', function () {
        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        $stream->findAllAfterLastOfType(EreignisWasTriggered::class);
    })->throws(\RuntimeException::class, 'No element of type ' . EreignisWasTriggered::class . ' found');
});
