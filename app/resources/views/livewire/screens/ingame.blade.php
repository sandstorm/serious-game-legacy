<div class="gameboard">
    <header class="gameboard__header">
        <h2>Spiel ID: {{ $gameId }}</h2>

        <x-gameboard.player-list :myself="$myself" />
    </header>

    <x-player-details :player-id="$currentPlayerId" :game-stream="$this->gameStream" />

    <div class="card-piles">
        @foreach($cardPiles as $pile)
            <x-card-pile :title="$pile" :game-stream="$this->gameStream" />
        @endforeach
    </div>

    <footer>
        @if ($this->getCurrentPlayer() == $myself)
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </footer>
</div>
