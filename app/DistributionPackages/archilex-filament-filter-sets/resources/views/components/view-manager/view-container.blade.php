@props([
    'viewGroup' => null,
    'viewGroupLabel' => null,
    'canBeReordered' => false,
])

<div class="flex flex-col">
    <div class="flex items-center justify-between min-h-[36px]">
        <h3 class="font-medium text-gray-400 dark:text-gray-500">
            {{ __('advanced-tables::advanced-tables.view_manager.subheadings.' . $viewGroupLabel ) }}
        </h3>
        @if ($canBeReordered)
            <div>
                <x-filament::icon-button
                    x-show="! reorderViewGroup || reorderViewGroup !== '{{ $viewGroup }}'"
                    x-on:click="reorderViewGroup = '{{ $viewGroup }}'"
                    icon="heroicon-m-arrows-up-down"
                    color="gray"
                />
                <x-filament::icon-button
                    x-show="reorderViewGroup && reorderViewGroup === '{{ $viewGroup }}'"
                    x-on:click="reorderViewGroup = null"
                    icon="heroicon-m-check"
                    color="gray"
                />
            </div>
        @endif
    </div>
    {{ $slot }}
</div>