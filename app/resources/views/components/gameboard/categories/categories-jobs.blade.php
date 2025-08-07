@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

<div class="card-pile">
    <button
        @class(["card"])
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
    </button>
</div>

@if ($this->jobOfferIsVisible)
    <x-job-offers-modal :player-id="$playerId" :game-events="$gameEvents"/>
@endif
