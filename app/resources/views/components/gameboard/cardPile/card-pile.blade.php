@props([
    'card' => null,
    'category' => null,
    'pileId' => null,
])

<div class="card-pile">
    <div class="shadow-card-1"></div>
    <div class="shadow-card-2"></div>
    <button class="card" aria-label="Karte anzeigen" wire:click="showCardActions('{{$card->getId()->value}}')">
        <div class="card__icon">
            <i class="icon-lupe" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">{{ $card->getTitle() }}</h4>

        <div class="card__content">
            <x-gameboard.resourceChanges.resource-changes :resource-changes="$card->getResourceChanges()" />
        </div>
    </button>
</div>

@if ($this->cardActionsVisible($card->getId()->value))
    <x-gameboard.cardPile.card-actions-modal :category="$category" :card="$card" :pile-id="$pileId" />
@endif
