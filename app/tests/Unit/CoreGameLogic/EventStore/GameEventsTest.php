<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\EventStore;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Spielzug\Event\TriggeredEreignis;

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
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new LebenszielId('Lebensziel XYZ'),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new LebenszielId('Lebensziel AAA'),
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
        expect($events[0]::class)->toBe(LebenszielChosen::class)
            ->and($events[0]->lebensziel->id->value)->toBe('Lebensziel XYZ')
            ->and($events[1]::class)->toBe(LebenszielChosen::class)
            ->and($events[1]->lebensziel->id->value)->toBe('Lebensziel AAA')
            ->and($events[2]::class)->toBe(GameWasStarted::class)
            ->and($events[2]->playerOrdering[0])->toBe($this->p1);
    });


    it('returns empty stream if no events are found', function () {
        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        expect($stream->findAllAfterLastOfType(GameWasStarted::class)->count())->toBe(0);
    });

    it('throws if no event of type is found', function () {
        $stream = $this->coreGameLogic->getGameStream($this->gameId);
        $stream->findAllAfterLastOfType(TriggeredEreignis::class);
    })->throws(\RuntimeException::class, 'No element of type ' . TriggeredEreignis::class . ' found');
});
