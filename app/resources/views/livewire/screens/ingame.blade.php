{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="gameboard">
    <header class="gameboard__header">
        <h2>Spiel ID: {{ $gameId }}</h2>

        <x-gameboard.player-list :myself="$myself" />
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-stream="$this->gameStream" />
        @endif
    </header>
    <div class="konjunktur-zyklus">
        Jahr: {{ $currentYear->value }} - {{ $konjunkturphasenDefinition->type }} <br />
        <button type="button" class="button button--type-primary" wire:click="showKonjunkturphaseDetails()">Zeige Details</button>
        @if ($konjunkturphaseDetailsVisible)
            <x-konjunkturphase-detais :game-stream="$this->gameStream" />
        @endif
    </div>
    <hr />

    <button type="button" class="button button--type-primary" wire:click="shuffleCards()">Shuffle</button>
    <div class="card-piles">
        @foreach($cardPiles as $pile)
            <x-card-pile :title="$pile" :game-stream="$this->gameStream" />
        @endforeach
    </div>

    <footer>
        @if ($this->currentPlayerIsMyself())
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </footer>
</div>
