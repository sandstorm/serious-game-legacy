@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div>
    @if (MoneySheetState::hasPlayerCompletedMoneysheet($this->gameEvents, $this->myself))
        <x-konjunkturphase.konjunkturphase-summary-modal :game-events="$this->gameEvents" :myself="$this->myself" />
    @elseif ($this->moneySheetIsVisible)
        <x-konjunkturphase.konjunkturphase-moneysheet-modal :player-id="$this->myself" :game-events="$this->gameEvents" :money-sheet="$this->getMoneysheetForPlayerId($this->myself)" />
    @else
        <x-konjunkturphase.konjunkturphase-over-modal />
    @endif
    <x-notification.notification/>
</div>
