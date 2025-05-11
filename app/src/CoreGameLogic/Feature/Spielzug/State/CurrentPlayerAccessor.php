<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasCompleted;

class CurrentPlayerAccessor
{
    public static function forStream(GameEvents $stream): PlayerId
    {
        $currentPlayerOrdering = $stream->findLast(GameWasStarted::class)->playerOrdering;

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
            throw new \RuntimeException('Previous player "' . $previousPlayer . '" not found in ordering');
        }

        $nextIndex = ((int) $index + 1) % count($currentPlayerOrdering);
        return $currentPlayerOrdering[$nextIndex];
    }
}
