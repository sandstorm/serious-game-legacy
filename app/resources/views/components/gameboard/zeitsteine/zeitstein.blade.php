@props([
    'playerColorClass' => null,
    'drawEmpty' => false,
])

<li @class([
    'zeitsteine__item',
    'zeitsteine__item--is-empty' => $drawEmpty,
    $playerColorClass
])></li>
