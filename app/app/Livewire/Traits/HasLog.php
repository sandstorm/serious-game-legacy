<?php

declare(strict_types=1);

namespace App\Livewire\Traits;


use App\Livewire\Dto\EventLogEntry;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\State\LogState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;

trait HasLog
{
    /**
     * @return EventLogEntry[]
     */
    private function getLogEntriesForPlayerLog(): array
    {
        return array_map(function ($logEntry) {
            return new EventLogEntry(
                playerName: PlayerState::getNameForPlayer($this->getGameEvents(), $logEntry->getPlayerId()),
                text: $logEntry->getText(),
                colorClass: PlayerState::getPlayerColorClass($this->getGameEvents(), $logEntry->getPlayerId()),
                resourceChanges: $logEntry->getResourceChanges(),
            );
        }, LogState::getLogEntries($this->getGameEvents()));
    }
}
