@props([
    'categories' => [],
])

<div class="categories">
    @foreach($categories as $category)
        <div class="category">
            <ul class="zeitsteine">
                @foreach($category->zeitsteine as $zeitstein)
                    <x-gameboard.zeitsteine.zeitstein-icon :player-name="$zeitstein->playerName" :player-color-class="$zeitstein->colorClass" :draw-empty="$zeitstein->drawEmpty" />
                @endforeach
            </ul>

            <div class="category__content">
                <x-dynamic-component :component="$category->componentName" :category="$category" :game-events="$gameEvents" :player-id="$playerId" />
            </div>
        </div>
    @endforeach
</div>
