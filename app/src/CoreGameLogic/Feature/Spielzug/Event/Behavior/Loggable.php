<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;


use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;

/**
 * This interface is usually applied on GameEvents which should add entries to the event-log that is visible to all players.
 */
interface Loggable
{
    public function getLogEntry(): LogEntry;
}
