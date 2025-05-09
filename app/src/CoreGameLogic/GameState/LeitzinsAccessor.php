<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\Event\JahreswechselEvent;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;

class LeitzinsAccessor
{

    public static function forStream(GameEvents $stream): Leitzins
    {
        $leitzins = $stream->reduce(function($state, $event) {
            if ($event instanceof JahreswechselEvent) {
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
