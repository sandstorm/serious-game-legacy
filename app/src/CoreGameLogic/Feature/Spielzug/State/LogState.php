<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;

class LogState
{
    /**
     * @param GameEvents $gameEvents
     * @return LogEntry[]
     */
    public static function getLogEntries(GameEvents $gameEvents): array
    {
        /** @var Loggable[] $logEvents */
        $logEvents = $gameEvents->filter(fn ($event) => $event instanceof Loggable);
        return array_map(fn ($event) => $event->getLogEntry(), $logEvents);
    }
}
