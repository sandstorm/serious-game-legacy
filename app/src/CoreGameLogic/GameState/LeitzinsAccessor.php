<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;

class LeitzinsAccessor
{

    public static function forStream(GameEvents $stream): Leitzins
    {
        $leitzins = $stream->reduce(function($state, $event) {
            if ($event instanceof NewYearWasStarted) {
                return $event->leitzins;
            }
            return $state;
        }, new \RuntimeException('No Leitzins found in stream'));

        if ($leitzins instanceof \RuntimeException) {
            throw $leitzins;
        }

        return $leitzins;
    }
}
