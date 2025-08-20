{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="game">
    <header class="game__header">
        <x-player-list :game-events="$this->gameEvents" :myself="$myself" :active-player="$this->getCurrentPlayer()"/>
    </header>

    <div class="game__content">
        <div class="game-board">
            <x-gameboard.kompetenzen-overview :game-events="$this->gameEvents" :player-id="$myself" />

            <div class="game-board__konjukturphase">
                <hr />
                <button type="button" class="button button--type-borderless"
                        wire:click="showKonjunkturphaseDetails()">
                    Konjunktur: {{ $konjunkturphasenDefinition->type }} <i class="icon-info" aria-hidden="true"></i>
                </button>
                <hr />
            </div>
            @if ($konjunkturphaseDetailsVisible)
                <x-konjunkturphase-details :game-events="$this->gameEvents"/>
            @endif

            <x-gameboard.categories :game-events="$this->gameEvents" :player-id="$myself" />
            <x-gameboard.instantActions.instanct-actions />
        </div>
    </div>

    <aside class="game__aside">
        <x-sidebar.sidebar :game-events="$this->gameEvents" :player-id="$myself" />
    </aside>

    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log/>
        @endif
    </div>

    <x-notification.notification/>
    <x-banner.banner/>

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
    @if ($isMinijobVisible)
        <x-minijob-modal :player-id="$myself" :game-events="$this->gameEvents"/>
    @endif
    @if ($isWeiterbildungVisible)
        <x-weiterbildung-modal :player-id="$myself" :game-events="$this->gameEvents"/>
    @endif
    @if ($showLebenszielForPlayer)
        <x-lebensziel-modal :player-id="$showLebenszielForPlayer" :game-events="$this->gameEvents" />
    @endif
    @if ($takeOutALoanIsVisible)
        <x-gameboard.moneySheet.take-out-loan-modal
            :game-events="$this->gameEvents"
            :player-id="$myself"
        />
    @endif
    @if ($this->sellInvestmentsModalIsVisible)
        <x-gameboard.sell-investments-modal
            :game-events="$this->gameEvents"
            :player-id="$myself"
        />
    @endif
    @if ($this->showItsYourTurnNotification)
        <x-gameboard.its-your-turn-notification />
    @endif
</div>
