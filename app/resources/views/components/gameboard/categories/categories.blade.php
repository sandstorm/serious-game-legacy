@props([
    'categories' => [],
])

<div class="game-board__categories">
    @foreach($categories as $category)
        <div class="game-board__category">
            <ul class="zeitsteine">
                @foreach($category->zeitsteine as $zeitstein)
                    <x-gameboard.zeitsteine.zeitstein-icon :player-name="$zeitstein->playerName" :player-color-class="$zeitstein->colorClass" :draw-empty="$zeitstein->drawEmpty" />
                @endforeach
            </ul>

            <x-dynamic-component :component="$category->componentName" :category="$category" :game-events="$gameEvents" :player-id="$playerId" />
        </div>
    @endforeach
</div>
