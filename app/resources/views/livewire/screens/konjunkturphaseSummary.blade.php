@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\PlayerId')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState')

<div class="konjunkturphase-summary">
    <ul class="konjunkturphase-summary__tabs">
        @foreach(PreGameState::playersWithNameAndLebensziel($this->gameEvents) as $player)
            <li @class(["konjunkturphase-summary__tab", "konjunkturphase-summary__tab--is-active" => $player->playerId->value === $this->summaryActiveTabId]) >
                <button id="{{$player->playerId}}" type="button" class="button" role="tab"
                        wire:click="showMoneysheetSummaryForPlayer('{{$player->playerId}}')">
                    {{$player->name}}
                    @if(MoneySheetState::hasPlayerCompletedMoneysheet($this->gameEvents, $player->playerId))
                        ✅
                    @else
                        ⏳
                    @endif
                </button>
            </li>
        @endforeach
    </ul>


    <x-konjunkturphase.summary :game-events="$this->gameEvents"
                               :player-id="PlayerId::fromString($this->summaryActiveTabId)"/>

    <button wire:click="showMoneySheet()"
            type="button"
            class="button button--type-primary">
        TODO
    </button>


    <div class="dev-bar">
        <button type="button" class="button button--type-primary" wire:click="showLog()">Log</button>
        @if ($isLogVisible)
            <x-gameboard.log/>
        @endif
    </div>
</div>
