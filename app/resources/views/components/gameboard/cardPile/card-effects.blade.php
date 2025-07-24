@props([
    'card' => null,
    'styleClass' => 'vertical', // vertical or horizontal
])

<div @class(["card__effects", "card__effects--$styleClass"])>
    @if ($card->resourceChanges->guthabenChange->value != 0)
        <div class="card__effect">{!! $card->resourceChanges->guthabenChange->formatWithIcon() !!}</div>
    @endif
    @if ($card->resourceChanges->zeitsteineChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Zeitsteine" :change="$card->resourceChanges->zeitsteineChange" iconClass="icon-fehler" />
        </div>
    @endif
    @if ($card->resourceChanges->bildungKompetenzsteinChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Bildung & Karriere Kompetenzen" :change="$card->resourceChanges->bildungKompetenzsteinChange" iconClass="icon-bildung-und-karriere" />
        </div>
    @endif
    @if ($card->resourceChanges->freizeitKompetenzsteinChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Freizeit & Soziales Kompetenzen" :change="$card->resourceChanges->freizeitKompetenzsteinChange" iconClass="icon-freizeit-und-soziales" />
        </div>
    @endif
</div>
