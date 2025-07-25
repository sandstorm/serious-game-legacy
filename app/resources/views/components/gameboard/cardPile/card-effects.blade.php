@props([
    'resourceChanges' => null,
    'styleClass' => 'vertical', // vertical or horizontal
])

<div @class(["card__effects", "card__effects--$styleClass"])>
    @if ($resourceChanges->guthabenChange->value != 0)
        <div class="card__effect">{!! $resourceChanges->guthabenChange->formatWithIcon() !!}</div>
    @endif
    @if ($resourceChanges->zeitsteineChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Zeitsteine" :change="$resourceChanges->zeitsteineChange" iconClass="icon-zeitstein" />
        </div>
    @endif
    @if ($resourceChanges->bildungKompetenzsteinChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Bildung & Karriere Kompetenzen" :change="$resourceChanges->bildungKompetenzsteinChange" iconClass="icon-bildung-und-karriere" />
        </div>
    @endif
    @if ($resourceChanges->freizeitKompetenzsteinChange)
        <div class="card__effect">
            <x-gameboard.cardPile.card-effect sr-label="Freizeit & Soziales Kompetenzen" :change="$resourceChanges->freizeitKompetenzsteinChange" iconClass="icon-freizeit-und-soziales" />
        </div>
    @endif
</div>
