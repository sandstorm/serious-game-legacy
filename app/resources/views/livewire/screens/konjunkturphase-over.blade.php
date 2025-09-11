@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div>
    @if (MoneySheetState::hasPlayerCompletedMoneysheet($this->getGameEvents(), $this->myself))
        @if($this->isSellInvestmentsToAvoidInsolvenzModalVisible)
            <x-konjunkturphase.konjunkturphase-sell-investments-to-avoid-insolvenz :game-events="$this->getGameEvents()" :player-id="$this->myself" />
        @elseif($this->isShowInformationForFiledInsolvenzModalVisible)
            <x-konjunkturphase.konjunkturphase-show-information-for-filed-insolvenz-modal />
        @else
            <x-konjunkturphase.konjunkturphase-summary-modal :game-events="$this->getGameEvents()" :player-id="$this->myself" />
        @endif
    @elseif ($this->moneySheetIsVisible)
        <x-konjunkturphase.konjunkturphase-moneysheet-modal :player-id="$this->myself" :game-events="$this->getGameEvents()" :money-sheet="$this->getMoneysheetForPlayerId($this->myself)" />
    @else
        <x-konjunkturphase.konjunkturphase-over-modal />
    @endif
    <x-notification.notification/>
    <x-banner.banner/>
</div>
