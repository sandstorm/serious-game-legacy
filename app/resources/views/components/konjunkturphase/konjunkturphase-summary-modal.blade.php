@extends ('components.modal.modal', ['type' => "borderless"])

@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\PlayerId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')

@props([
    'gameEvents' => null,
    'myself' => null,
])

@section('icon')
    <i class="icon-phasenwechsel" aria-hidden="true"></i> Zusammenfassung
@endsection

@section('content')
    <div class="tabs">
        <ul role="tablist" class="tabs__list">
            @foreach(PreGameState::playersWithNameAndLebensziel($gameEvents) as $player)
                <li @class(["tabs__list-item", "tabs__list-item--active" => $player->playerId->value === $this->summaryActiveTabId]) >
                    <button
                        type="button"
                        class="button button--type-borderless"
                        role="tab"
                        wire:click="showMoneysheetSummaryForPlayer('{{$player->playerId}}')"
                    >
                        {{$player->name}}
                        @if(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $player->playerId))✅@else⏳@endif
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tabs__upper-content">
            {{$this->summaryActiveTabId}}
            <x-konjunkturphase.konjunkurphase-summary
                :money-sheet="$this->getMoneysheetForPlayerId(PlayerId::fromString($this->summaryActiveTabId))"
            />
        </div>
    </div>
@endsection

@section('footer')
    @if(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $myself) === false)
        <button
            wire:click="markPlayerAsReady()"
            type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass(),
            ])
        >
            Ich bin fertig
        </button>
    @else
        <span>Warte, bis alle fertig sind</span>
    @endif
@endsection
