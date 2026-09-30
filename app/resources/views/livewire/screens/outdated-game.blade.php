{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="outdated-game">
    <h1>Dieses Spiel kann nicht fortgesetzt werden</h1>
    <p>
        Das Spiel wurde mit einer anderen Version des Spiels erstellt und kann deshalb nicht fortgesetzt werden.
    </p>
    <a class="button button--type-primary" href="{{ route('game-play.index') }}">Zur Spielübersicht</a>
</div>
