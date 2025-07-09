@props([
    'categories' => [],
])

<div class="game-board__categories">
    @foreach($categories as $category)
        <div class="game-board__category">
            <x-gameboard.categories.categories-header :category="$category" :game-events="$gameEvents"/>
            <x-dynamic-component :component="$category->componentName" :category="$category" :game-events="$gameEvents" :player-id="$playerId" />
        </div>
    @endforeach
</div>
