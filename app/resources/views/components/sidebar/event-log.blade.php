<h2 class="event-log__heading">Ereignisprotokoll:</h2>

<div
    @class([
        "event-log",
        $this->currentPlayerIsMyself() ? "" : "event-log--open"
    ])
    :class="eventLogOpen ? 'event-log--open' : ''"
>
    @if ($this->currentPlayerIsMyself())
        <button class="event-log__toggle button button--type-icon" :aria-expanded="eventLogOpen" id="event-log-trigger" aria-controls="event-log-entries" x-on:click="eventLogOpen = !eventLogOpen">
            <i :class="eventLogOpen ? 'icon-close' : 'icon-log-ausklappen'" aria-hidden="true"></i>
            <span class="sr-only">Ereignisprotokoll ausklappen/einklappen</span>
        </button>
    @endif

    <ul class="event-log__entries" id="event-log-entries" aria-labelledby="event-log-trigger">
        @forelse(array_reverse($this->getLogEntriesForPlayerLog()) as $logEntry)
            <li class="event-log__entry">
                <strong @class(["event-log__entry-player-name", $logEntry->colorClass])>{{$logEntry->playerName}}</strong>
                <span class="event-log__entry-text">
                    {{ $logEntry->text }}
                </span>
                @if($logEntry->resourceChanges !== null)
                    <x-gameboard.resourceChanges.resource-changes :resource-changes="$logEntry->resourceChanges" style-class="horizontal" />
                @endif
            </li>
        @empty
            <li class="event-log__entry event-log__entry--empty">
                <span class="event-log__entry-text">Keine Ereignisse vorhanden.</span>
            </li>
        @endforelse
    </ul>
</div>
