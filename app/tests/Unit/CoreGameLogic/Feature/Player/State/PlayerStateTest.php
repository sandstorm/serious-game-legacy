<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->playerId1 = PlayerId::fromString('p1');
    $this->playerId2 = PlayerId::fromString('p2');
    $this->stream = GameEvents::fromArray([
        new PreGameStarted(
            playerIds: [$this->playerId1, $this->playerId2],
            resourceChanges: new ResourceChanges(
                guthabenChange: 50000,
                zeitsteineChange: 3
            ),
        ),
        new GameWasStarted(
            playerOrdering: [
                $this->playerId1,
                $this->playerId2,
            ]
        ),
        new CardWasActivated(
            $this->playerId1,
            PileId::BILDUNG_PHASE_1,
            new CardId('test1'),
            new ResourceChanges(
                guthabenChange: -500,
                zeitsteineChange: -1,
            )),
        new CardWasActivated(
            $this->playerId2,
            PileId::BILDUNG_PHASE_1,
            new CardId('test2'),
            new ResourceChanges(
                guthabenChange: -100,
            ))
    ]);
});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        expect(PlayerState::getZeitsteineForPlayer($this->stream, $this->playerId1))->toBe(2)
            ->and(PlayerState::getZeitsteineForPlayer($this->stream, $this->playerId2))->toBe(3);
    });

    it('Throws an exception if the player does not exist', function () {
        PlayerState::getZeitsteineForPlayer($this->stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

describe('getGuthabenForPlayer', function () {
    it('returns the correct number', function () {
        expect(PlayerState::getGuthabenForPlayer($this->stream,$this->playerId1))->toBe(49500)
            ->and(PlayerState::getGuthabenForPlayer($this->stream, $this->playerId2))->toBe(49900);
    });

    it('Throws an exception if the player does not exist', function () {
        PlayerState::getGuthabenForPlayer($this->stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

describe('getForPlayer', function () {
    it('returns the correct number', function () {
        expect(PlayerState::getGuthabenForPlayer($this->stream,$this->playerId1))->toBe(49500)
            ->and(PlayerState::getGuthabenForPlayer($this->stream, $this->playerId2))->toBe(49900);
    });

    it('Throws an exception if the player does not exist', function () {
        PlayerState::getGuthabenForPlayer($this->stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});
