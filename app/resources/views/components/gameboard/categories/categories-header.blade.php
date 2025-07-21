@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'category' => null,
    'gameEvents' => null,
])

<h3>{{ $category->title }}</h3>
<ul class="zeitsteine">
    @foreach($category->zeitsteine as $zeitstein)
        <x-gameboard.zeitsteine.zeitstein :player-color-class="$zeitstein->colorClass" :draw-empty="$zeitstein->drawEmpty" />
    @endforeach
</ul>

<ul class="kompetenzen">
    @for($i = 0; $i < $category->kompetenzen; $i++)
        <li class="kompetenz"></li>
    @endfor

    @for($i = 0; $i < $category->kompetenzenRequiredByPhase; $i++)
        <li class="kompetenz kompetenz--empty"></li>
    @endfor
</ul>
