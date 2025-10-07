@extends ('components.modal.modal', ['type' => "borderless"])

@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')
@use('Domain\CoreGameLogic\PlayerId')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')

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
                        @if(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $player->playerId))
                            <i class="icon-fertig" aria-hidden="true"></i>
                            <span class="sr-only">Spieler ist bereit für den Konjunkturphasenwechsel.</span>
                        @else
                            <i class="icon-sanduhr" aria-hidden="true"></i>
                            <span class="sr-only">Spieler ist noch nicht bereit für den Konjunkturphasenwechsel.</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tabs__upper-content">
            <x-konjunkturphase.konjunkurphase-summary
                :money-sheet="$this->getMoneysheetForPlayerId(PlayerId::fromString($this->summaryActiveTabId))"
                :game-events="$gameEvents"
                :player-id="PlayerId::fromString($this->summaryActiveTabId)"
                :myself="$myself"
            />
        </div>
    </div>
@endsection

@section('footer')
    @if(PlayerState::isPlayerInsolvent($gameEvents, $myself))
        <button
            wire:click="toggleShowInformationForFiledInsolvenzModal()"
            type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass(),
            ])
        >
            Informationen zur Privatinsolvenz
        </button>
    @endif

    @if($this->canFileInsolvenzForPlayer()->canExecute)
        <button
            wire:click="fileInsolvenzForPlayer()"
            type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass(),
            ])
        >
            Insolvenz anmelden
        </button>
    @elseif(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $myself) === false)
        <button
            wire:click="markPlayerAsReady()"
            type="button"
            @class([
                "button",
                "button--type-primary",
                "button--disabled" => !$this->canMarkPlayerAsReady()->canExecute,
                $this->getPlayerColorClass(),
            ])
        >
            Ich bin fertig
        </button>
    @else
        <span>Warte, bis alle fertig sind</span>
    @endif
@endsection
