<?php

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\Event\InitializePlayerOrdering;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

class CurrentPlayerAccessor
{

    public static function forStream(\Domain\CoreGameLogic\Dto\Event\EventStream $stream): PlayerId
    {
        $currentPlayerOrdering = $stream->findLast(InitializePlayerOrdering::class)->playerOrdering;

        $previousPlayer = $stream->findLastOrNull(SpielzugWasCompleted::class)?->player;

        if (!$previousPlayer) {
            // Initial move -> first according to player ordering
            return reset($currentPlayerOrdering);
        }

        $index = null;
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
