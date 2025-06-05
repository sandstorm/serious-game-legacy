@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <x-gameboard.player-list :myself="$myself" />
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-stream="$this->gameStream" />
        @endif
    </header>

    <div class="game__content">
        <div class="game-board">
            <div class="game-board__konjukturphase">
                Jahr: {{ $currentYear->value }} - {{ $konjunkturphasenDefinition->type }}
                <button type="button" class="button button--type-primary button--size-small" wire:click="showKonjunkturphaseDetails()">Zeige Details</button>
                @if ($konjunkturphaseDetailsVisible)
                    <x-konjunkturphase-detais :game-stream="$this->gameStream" />
                @endif
            </div>

            <div class="game-board__categories">
                @foreach($cardPiles as $pile)
                    <div class="game-board__category">
                        <h3>{{ $pile }}</h3>
                        <x-card-pile :title="$pile" :game-stream="$this->gameStream" />
                    </div>
                @endforeach
                <div class="game-board__category">
                    <h3>Jobs</h3>
                </div>

                <div class="game-board__category">
                    <h3>Investitionen</h3>
                </div>
            </div>
        </div>
    </div>

    <aside class="game__aside">
        <h4>Money Sheet</h4>
        <button class="button button--type-primary" wire:click="showMoneySheet()">{{ PlayerState::getGuthabenForPlayer($this->gameStream(), $myself) }}€</button>
        @if ($moneySheetIsVisible)
            <x-money-sheet :player-id="$myself" :game-stream="$this->gameStream"/>
        @endif

        @if ($this->currentPlayerIsMyself())
            <button type="button" class="button button--type-primary" wire:click="spielzugAbschliessen()">Spielzug abschließen</button>
        @endif
    </aside>
</div>
