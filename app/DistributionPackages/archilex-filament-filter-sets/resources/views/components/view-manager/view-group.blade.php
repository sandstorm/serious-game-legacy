@props([
    'canBeManaged' => false,
    'isFavorite' => true,
    'model' => null,
    'views' => null,
])

<div
    @if ($canBeManaged)
        x-on:end.stop="reorderingViews = null, $wire.reorderViews($event.target.sortable.toArray(), @js($model), @js($isFavorite))"
        x-on:start="reorderingViews = @js($views)"
        x-sortable
    @endif
    class="flex flex-col gap-y-1"
>
    {{ $slot }}
</div>