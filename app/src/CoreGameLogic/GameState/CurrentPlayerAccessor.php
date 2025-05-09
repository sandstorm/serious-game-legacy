<?php

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\Event\InitializePlayerOrdering;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

class CurrentPlayerAccessor
{

    public static function forStream(GameEvents $stream): PlayerId
    {
        $currentPlayerOrdering = $stream->findLast(InitializePlayerOrdering::class)->playerOrdering;

        $previousPlayer = $stream->findLastOrNull(SpielzugWasCompleted::class)?->player;

        if ($previousPlayer === null) {
            // Initial move -> first according to player ordering
            return $currentPlayerOrdering[0];
        }

        $index = null;
        /** @var int $i */
        foreach ($currentPlayerOrdering as $i => $player) {
            assert($player instanceof PlayerId);
            if ($player->equals($previousPlayer)) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \RuntimeException('Previous player not found in ordering');
        }

        $nextIndex = ($index + 1) % count($currentPlayerOrdering);
        return $currentPlayerOrdering[$nextIndex];
    }
}
