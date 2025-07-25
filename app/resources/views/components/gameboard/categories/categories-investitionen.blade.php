@props([
    'category' => null,
    'gameEvents' => null,
    '$playerId' => null,
])

<div class="card-pile">
    <div
        @class(["card", !$this->canRequestJobOffers()->canExecute ? "card--disabled" : ""])
        role="button"
        aria-label="Investitionen anschauen"
        wire:click="toggleInvestitionen()"
    >
        <div class="card__icon">
            <i class="icon-dots" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">Investitionen</h4>
        <div class="card__content card__content--investitionen">
            <i class="icon-aktien" aria-hidden="true"></i>
            <i class="icon-ETF" aria-hidden="true"></i>
            <i class="icon-immobilien" aria-hidden="true"></i>
            <i class="icon-krypto" aria-hidden="true"></i>
        </div>
    </div>
</div>

@if ($this->showInvestitionen)
    <x-gameboard.investitionen.invenstitionen-modal :game-events="$gameEvents" :player-id="$playerId" />
@endif
