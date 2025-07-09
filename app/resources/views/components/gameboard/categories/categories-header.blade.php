@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'category' => null,
    'gameEvents' => null,
])

<h3>{{ $category->title }}</h3>
<ul class="zeitsteine">
    @foreach($category->placedZeitsteine as $placedZeitstein)
        @for($i = 0; $i < $placedZeitstein->zeitsteine; $i++)
            <li class="zeitsteine__item" @style(['background-color:' . PlayerState::getPlayerColor($gameEvents, $placedZeitstein->playerId)])></li>
        @endfor
    @endforeach
    @for($i = 0; $i < $category->availableZeitsteine; $i++)
        <li class="zeitsteine__item zeitsteine__item--empty"></li>
    @endfor
</ul>

<ul class="kompetenzen">
    @for($i = 0; $i < $category->kompetenzen; $i++)
        <li class="kompetenz"></li>
    @endfor

    @for($i = 0; $i < $category->kompetenzenRequiredByPhase; $i++)
        <li class="kompetenz kompetenz--empty"></li>
    @endfor
</ul>
