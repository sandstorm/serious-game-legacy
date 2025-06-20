@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
<div class="konjunkturphase-end">
    <div class="konjunkturphase-end__info">
        Die Konjunkturphase "{{KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents())->type->value}}" ist
        zu ende.
    </div>
    <button wire:click="showMoneySheet()"
            type="button"
            class="button button--type-primary">
        Weiter
    </button>
    @if ($moneySheetIsVisible)
        @if ($editIncomeIsVisible)
            <x-gameboard.moneySheet.money-sheet-income-modal :player-id="$myself" :game-events="$this->gameEvents"/>
        @elseif ($editExpensesIsVisible)
            <x-gameboard.moneySheet.money-sheet-expenses-modal :player-id="$myself"
                                                               :game-events="$this->gameEvents"/>
        @else
            <x-money-sheet.money-sheet :player-id="$myself" :game-events="$this->gameEvents"/>
        @endif
    @endif


    <div class="dev">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log />
        @endif
    </div>
</div>
