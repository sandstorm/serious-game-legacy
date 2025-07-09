@props([
    'category' => null,
    'gameEvents' => null,
])

<x-card-pile :category="$category->title->value" :card-pile="$category->cardPile->value"
     :game-events="$gameEvents"/>
