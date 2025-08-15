<div class="sidebar__event-log">
    <h2>Ereignisprotokoll:</h2>
    <ul @class(["sidebar__event-log-entries", $isPlayersTurn ? "sidebar__event-log-entries--is-players-turn": ""])>
        @foreach(array_reverse($this->getLogEntriesForPlayerLog()) as $logEntry)
            <li class="sidebar__event-log-entry">
                <strong @class(["event-log-entry__player-name", $logEntry->colorClass])>{{$logEntry->playerName}}</strong>
                <span class="event-log-entry__text">{{$logEntry->text}}</span>
                @if($logEntry->resourceChanges !== null)
                    <x-gameboard.resourceChanges.resource-changes :resource-changes="$logEntry->resourceChanges" style-class="horizontal" />
                @endif
            </li>
        @endforeach
    </ul>
</div>
