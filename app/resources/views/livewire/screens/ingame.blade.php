@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <x-player-list :game-events="$this->gameEvents" :myself="$myself" :active-player="$this->getCurrentPlayer()"/>
        @if ($showDetailsForPlayer)
            <x-player-details :player-id="$showDetailsForPlayer" :game-events="$this->gameEvents" :myself="$myself"/>
        @endif
    </header>

    <div class="game__content">
        <div class="game-board">
            <x-gameboard.kompetenzen-overview :game-events="$this->gameEvents" :player-id="$myself" />

            <div class="game-board__konjukturphase">
                <hr />
                <button type="button" class="button button--type-text"
                        wire:click="showKonjunkturphaseDetails()">
                    Konjunktur: {{ $konjunkturphasenDefinition->type }} <i class="icon-info"></i>
                </button>
                <hr />
            </div>
            @if ($konjunkturphaseDetailsVisible)
                <x-konjunkturphase-details :game-events="$this->gameEvents"/>
            @endif

            <x-gameboard.categories :game-events="$this->gameEvents" :player-id="$myself" />
        </div>
    </div>

    <aside class="game__aside">
        <p>
            <strong>Lebensziel:</strong> {{ PlayerState::getLebenszielDefinitionForPlayer($this->gameEvents, $myself)->name }}
        </p>

        @if ($this->currentPlayerIsMyself())
            <button class="button button--type-primary" wire:click="showTakeOutALoan()">
                Kredit aufnehmen
            </button>
            <button class="button button--type-primary" wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">
                Versicherung abschließen
            </button>
            <hr/>
            <button
                type="button"
                @class([
                    "button",
                    "button--type-primary",
                    "button--disabled" => !$this->canEndSpielzug()->canExecute,
                ])
                wire:click="spielzugAbschliessen()">
                Spielzug abschließen
            </button>
        @endif
    </aside>
    <x-notification.notification/>

    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log/>
        @endif
    </div>

    @if($isEreignisCardVisible)
        <x-ereignis-modal :game-events="$this->gameEvents" :player-id="$myself" />
    @endif
    @if ($moneySheetIsVisible)
        @if ($editIncomeIsVisible)
            <x-gameboard.moneySheet.money-sheet-income-modal
                :money-sheet="$this->getMoneysheetForPlayerId($myself)"
                :game-events="$this->gameEvents"
                :player-id="$myself"
            />
        @elseif ($editExpensesIsVisible)
            <x-gameboard.moneySheet.money-sheet-expenses-modal
                :money-sheet="$this->getMoneysheetForPlayerId($myself)"
                :game-events="$this->gameEvents"
                :player-id="$myself"
            />
        @else
            <x-money-sheet.money-sheet :money-sheet="$this->getMoneysheetForPlayerId($myself)"/>
        @endif
    @endif

</div>
