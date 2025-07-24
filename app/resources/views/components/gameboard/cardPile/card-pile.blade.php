@props([
    'card' => null,
    'category' => null,
])

<div class="card-pile">
    <div class="shadow-card-1"></div>
    <div class="shadow-card-2"></div>
    <div class="card" role="button" aria-label="Karte anzeigen" wire:click="showCardActions('{{$card->id->value}}')">
        <div class="card__icon">
            <i class="icon-lupe" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">{{ $card->title }}</h4>

        <div class="card__content">
            <x-gameboard.cardPile.card-effects :card="$card" />
        </div>
    </div>
</div>

@if ($this->cardActionsVisible($card->id->value))
    <x-gameboard.cardPile.card-actions-modal :category="$category" :card="$card" />
@endif
