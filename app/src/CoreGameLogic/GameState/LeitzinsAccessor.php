<?php

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\Event\JahreswechselEvent;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;

class LeitzinsAccessor
{

    public static function forStream(\Domain\CoreGameLogic\Dto\Event\EventStream $stream): Leitzins
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
