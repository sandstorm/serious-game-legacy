@props([
    'card' => null,
    'category' => null,
])

<div class="card-pile">
    <div class="card" wire:click="toggleCardActionsModal()">
        <div class="card__icon">
            <i class="icon-lupe"></i>
        </div>
        <h4 class="card__title">{{ $card->title }}</h4>

        <div class="card__effects">
            @if ($card->resourceChanges->guthabenChange->value != 0)
                <div class="card__effect">{!! $card->resourceChanges->guthabenChange->formatWithIcon() !!}</div>
            @endif
            @if ($card->resourceChanges->zeitsteineChange)
                <div class="card__effect">
                    <x-gameboard.cardPile.card-effects :change="$card->resourceChanges->zeitsteineChange" iconClass="icon-fehler" />
                </div>
            @endif
            @if ($card->resourceChanges->bildungKompetenzsteinChange)
                <div class="card__effect">
                    <x-gameboard.cardPile.card-effects :change="$card->resourceChanges->bildungKompetenzsteinChange" iconClass="icon-bildungundkarriere" />
                </div>
            @endif
            @if ($card->resourceChanges->freizeitKompetenzsteinChange)
                <div class="card__effect">
                    <x-gameboard.cardPile.card-effects :change="$card->resourceChanges->freizeitKompetenzsteinChange" iconClass="icon-freizeitundsoziales" />
                </div>
            @endif
        </div>
    </div>
</div>

@if ($this->cardActionsModalIsVisible)
    <x-gameboard.cardPile.card-actions-modal :category="$category" :card="$card" />
@endif
