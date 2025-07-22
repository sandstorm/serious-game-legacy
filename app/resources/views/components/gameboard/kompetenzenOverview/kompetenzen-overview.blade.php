@props([
    'categories' => [],
])

<div class="game-board__kompetenzen-overview">
    @foreach($categories as $category)
        <div>
            <h4>{{ $category->title }}</h4>

            <ul class="kompetenzen">
                @for($i = 0; $i < $category->kompetenzen; $i++)
                    <li class="kompetenz"></li>
                @endfor

                @for($i = 0; $i < $category->kompetenzenRequiredByPhase; $i++)
                    <li class="kompetenz kompetenz--empty"></li>
                @endfor
            </ul>
        </div>
    @endforeach
</div>
