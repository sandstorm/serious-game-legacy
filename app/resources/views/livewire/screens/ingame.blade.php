<div class="gameboard">
    <header class="gameboard__header">
        <h2>Spiel ID: {{ $gameId }}</h2>

        <x-gameboard.playerList :myself="$myself" />
    </header>

    <x-player-details :player-id="$currentPlayerId" :game-stream="$this->gameStream" />

    <footer>
        @if ($this->getCurrentPlayer() == $myself)
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </footer>
</div>
