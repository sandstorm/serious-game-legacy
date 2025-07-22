@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'jobDefinition' => null,
])

<button
    type="button"
    @class([
        "button",
        "button--type-primary",
        "button--disabled" => !$this->canRequestJobOffers()->canExecute,
    ])
    wire:click="showJobOffer()">
    Jobangebote anschauen (-1 Zeitstein)
</button>

@if ($jobDefinition !== null)
    <hr/>
    <button class="button button--type-outline-primary"
            wire:click="showIncomeTab('salary')">
        <ul class="zeitsteine">
            <li>-{{ $jobDefinition->requirements->zeitsteine }}</li>
            <x-gameboard.zeitsteine.zeitstein-icon :player-color-class="PlayerState::getPlayerColorClass($gameEvents, $playerId)" />
        </ul>
        <span>Mein Job. {!! PlayerState::getBaseGehaltForPlayer($gameEvents, $playerId)->format() !!}</span>
    </button>
@endif
@if ($this->jobOfferIsVisible)
    <x-job-offers-modal :player-id="$playerId" :game-events="$gameEvents"/>
@endif
<button
    type="button"
    @class([
        "minijob__button",
        "button",
        "button--type-primary",
        "button--disabled" => !$this->canDoMinijob(),
    ])
    wire:click="showMinijob()">
    Minijob ausführen (-1 Zeitstein)
</button>

@if ($this->isMinijobVisible)
    <x-minijob-modal :player-id="$playerId" :game-events="$gameEvents"/>
@endif
