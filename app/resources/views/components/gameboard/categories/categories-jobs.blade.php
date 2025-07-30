@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'jobDefinition' => null,
])

<div class="card-pile">
    <div
        @class(["card", !$this->canRequestJobOffers()->canExecute ? "card--disabled" : ""])
        role="button"
        aria-label="Jobangebote anschauen"
        wire:click="showJobOffers()"
    >
        <div class="card__icon">
            <i class="icon-dots" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">Jobbörse</h4>
        <div class="card__content card__content--jobs">
            <i class="icon-jobboerse" aria-hidden="true"></i>
        </div>
    </div>
</div>

@if ($jobDefinition !== null)
    <hr/>
    <button class="button button--type-outline-primary"
            wire:click="showIncomeTab('salary')">
        <ul class="zeitsteine">
            <li>-{{ $jobDefinition->getRequirements()->zeitsteine }}</li>
            <x-gameboard.zeitsteine.zeitstein-icon :player-color-class="PlayerState::getPlayerColorClass($gameEvents, $playerId)" />
        </ul>
        <span>Mein Job. {!! PlayerState::getCurrentGehaltForPlayer($gameEvents, $playerId)->format() !!}</span>
    </button>
@endif
@if ($this->jobOfferIsVisible)
    <x-job-offers-modal :player-id="$playerId" :game-events="$gameEvents"/>
@endif
