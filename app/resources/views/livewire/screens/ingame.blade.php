{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="gameboard">
    <header class="gameboard__header">
        <h2>Spiel ID: {{ $gameId }}</h2>

        <x-gameboard.player-list :myself="$myself" />
    </header>

    <button type="button" class="button button--type-primary" wire:click="shuffleCards()">Shuffle</button>

    <x-player-details :player-id="$currentPlayerId" :game-stream="$this->gameStream" />

    <div class="card-piles">
        @foreach($cardPiles as $pile)
            <x-card-pile :title="$pile" :game-stream="$this->gameStream" />
        @endforeach
    </div>

    <div class="konjunktur-zyklus">
        Jahr: {{ $konjunkturzyklus->year->value }} - {{ $konjunkturzyklus->type }} <br />
        {{ $konjunkturzyklusDefinition->description }}
    </div>

    <footer>
        @if ($this->getCurrentPlayer() == $myself)
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </footer>
</div>
