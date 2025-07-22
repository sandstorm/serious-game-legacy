@props([
    'playerName' => null,
    'playerColorClass' => null,
    'drawEmpty' => false,
])

<li @class([
    'zeitsteine__item',
    'zeitsteine__item--is-empty' => $drawEmpty,
    $playerColorClass
])>
    @if ($drawEmpty)
        <img src="{{ asset('images/zeitstein-empty.svg') }}" alt="Leerer Zeitstein" class="zeitsteine__item-image">
    @else
        <img src="{{ asset('images/zeitstein-'.$playerColorClass.'.svg') }}" alt="Zeitstein von {{$playerName}}" class="zeitsteine__item-image">
    @endif
</li>
