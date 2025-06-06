<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\EventStore;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectPlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\LebenszielFinder;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;

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
        lebensziel: LebenszielId::create(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: LebenszielId::create(2),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
        playerId: $this->p1,
        playerColor: new PlayerColor('#000'),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
        playerId: $this->p2,
        playerColor: new PlayerColor('#FFF'),
    ));
    $this->coreGameLogic->handle($this->gameId, StartGame::create());
});

describe('find all after last of type', function () {
    it('finds all elements after last event of type', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $eventsAfterSelectedEvent = $stream->findAllAfterLastOfType(NameForPlayerWasSet::class);

        expect($eventsAfterSelectedEvent->count())->toBe(5);

        $events = iterator_to_array($eventsAfterSelectedEvent->getIterator());

        $expectedLebenszielForP1 = LebenszielFinder::findLebenszielById(LebenszielId::create(1));
        $expectedLebenszielForP2 = LebenszielFinder::findLebenszielById(LebenszielId::create(2));

        expect($events[0]::class)->toBe(LebenszielWasSelected::class)
            ->and($events[0]->lebensziel->name)->toBe($expectedLebenszielForP1->name)
            ->and($events[1]::class)->toBe(LebenszielWasSelected::class)
            ->and($events[1]->lebensziel->name)->toBe($expectedLebenszielForP2->name)
            ->and($events[2]::class)->toBe(PlayerColorWasSelected::class)
            ->and($events[2]->playerColor->value)->toBe("#000")
            ->and($events[3]::class)->toBe(PlayerColorWasSelected::class)
            ->and($events[3]->playerColor->value)->toBe("#FFF")
            ->and($events[4]::class)->toBe(GameWasStarted::class)
            ->and($events[4]->playerOrdering[0])->toBe($this->p1);
    });


    it('returns empty stream if no events are found', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($stream->findAllAfterLastOfType(GameWasStarted::class)->count())->toBe(0);
    });

    it('throws if no event of type is found', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $stream->findAllAfterLastOfType(EreignisWasTriggered::class);
    })->throws(\RuntimeException::class, 'No element of type ' . EreignisWasTriggered::class . ' found');
});
