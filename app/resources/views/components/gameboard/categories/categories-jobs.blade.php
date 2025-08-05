@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

<div class="card-pile">
    <button
        @class(["card"])
        aria-label="Jobangebote anschauen (kostet 1 Zeitstein)"
        wire:click="showJobOffers()"
    >
        <div class="card__icon">
            <i class="icon-dots" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">Jobbörse</h4>
        <div class="card__content">
            <div class="resource-changes">
                <x-gameboard.resourceChanges.resource-change sr-label="Zeitsteine" change="-1" iconClass="icon-zeitstein" />
            </div>
        </div>
    </button>
</div>

@if ($this->jobOfferIsVisible)
    <x-job-offers-modal :player-id="$playerId" :game-events="$gameEvents"/>
@endif
