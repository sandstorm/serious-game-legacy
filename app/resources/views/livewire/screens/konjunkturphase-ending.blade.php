@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
<div class="konjunkturphase-end">

    @if ($moneySheetIsVisible)
        @if ($editIncomeIsVisible)
            <x-gameboard.moneySheet.income.money-sheet-income :money-sheet="$this->getMoneysheetForPlayerId($myself)"
                                                              :player-id="$myself" :game-events="$this->gameEvents"/>
        @elseif ($editExpensesIsVisible)
            <x-gameboard.moneySheet.expenses.money-sheet-expenses
                :money-sheet="$this->getMoneysheetForPlayerId($myself)" :player-id="$myself"
                :game-events="$this->gameEvents"/>
        @else
            <x-gameboard.moneySheet.money-sheet :money-sheet="$this->getMoneysheetForPlayerId($myself)"/>
        @endif
        <button
            type="button"
            @class([
                "button",
                "button--type-primary",
                "button--disabled" => !$this->canCompleteMoneysheet(),
            ])
            wire:click="completeMoneysheetForPlayer()"
        >
            weiter
        </button>
    @else
        <div class="konjunkturphase-end__info">
            Die Konjunkturphase "{{KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents())->type->value}}"
            ist
            zu Ende.
        </div>
        <button wire:click="showMoneySheet()"
                type="button"
                class="button button--type-primary">
            Weiter
        </button>
    @endif
    <x-notification.notification/>


    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log/>
        @endif
    </div>
</div>
