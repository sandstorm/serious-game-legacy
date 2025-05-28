{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <h2>Spiel ID: {{ $gameId }}</h2>

        <x-gameboard.player-list :myself="$myself" />
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-stream="$this->gameStream" />
        @endif
    </header>
    <div class="game__board">
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
    </div>
    <aside class="game__aside">
        <button>Money Sheet<br/> xx.xxx€</button>
        <div>
            <h2>Eventlog</h2>
            <ul>
                <li>Lorem</li>
                <li>Ipsum</li>
                <li>Dolor</li>
            </ul>
        </div>
    </aside>

    <footer class="game__footer">
        @if ($this->currentPlayerIsMyself())
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </footer>
</div>
