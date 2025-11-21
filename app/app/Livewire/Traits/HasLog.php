<?php

declare(strict_types=1);

namespace App\Livewire\Traits;


use App\Livewire\Dto\EventLogEntry;
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
            $playerId = $logEntry->getPlayerId();
            return new EventLogEntry(
                text: $logEntry->getText(),
                colorClass: $playerId !== null ? PlayerState::getPlayerColorClass($this->getGameEvents(), $playerId) : null,
                playerName: $playerId !== null ? PlayerState::getNameForPlayer($this->getGameEvents(), $playerId) : null,
                resourceChanges: $logEntry->getResourceChanges(),
            );
        }, LogState::getLogEntries($this->getGameEvents()));
    }
}
