@props([
    'resourceChanges' => null,
    'styleClass' => 'vertical', // vertical or horizontal
])

<div @class(["resource-changes", "resource-changes--$styleClass"])>
    @if ($resourceChanges->guthabenChange->value != 0)
        <div class="resource-change">{!! $resourceChanges->guthabenChange->formatWithIcon() !!}</div>
    @endif
    @if ($resourceChanges->zeitsteineChange)
        <x-gameboard.resourceChanges.resource-change sr-label="Zeitsteine" :change="$resourceChanges->zeitsteineChange" iconClass="icon-zeitstein" />
    @endif
    @if ($resourceChanges->bildungKompetenzsteinChange)
        <x-gameboard.resourceChanges.resource-change sr-label="Bildung & Karriere Kompetenzen" :change="$resourceChanges->bildungKompetenzsteinChange" iconClass="icon-bildung-und-karriere" />
    @endif
    @if ($resourceChanges->freizeitKompetenzsteinChange)
        <x-gameboard.resourceChanges.resource-change sr-label="Freizeit & Soziales Kompetenzen" :change="$resourceChanges->freizeitKompetenzsteinChange" iconClass="icon-freizeit-und-soziales" />
    @endif
</div>
