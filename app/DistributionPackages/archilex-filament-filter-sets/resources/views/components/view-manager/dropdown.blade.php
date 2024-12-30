@php
    use Archilex\AdvancedTables\Support\Config;
@endphp

@props([
    'offset' => 8,
    'color' => 'gray',
    'icon' => Config::getViewManagerIcon(),
    'iconPosition' => Config::getViewManagerIconPosition(),
    'outlined' => Config::showViewManagerButtonOutlined(),
    'label' => Config::getViewManagerButtonLabel(),
    'size' => Config::getViewManagerButtonSize(),
    'placement' => 'bottom-end',
])

<x-filament::dropdown
    :placement="$placement"
    width="xs"
    max-height="600px"
    :offset="$offset"
>
    <x-slot name="trigger">
        @if (Config::showViewManagerAsButton())
            <x-filament::button
                :icon="$icon"
                :icon-position="$iconPosition"
                :color="$color"
                :size="$size"
                :outlined="$outlined"
                class="advanced-tables-show-view-manager"
            >
                {{ $label }}
                @if (Config::hasViewManagerBadge())
                    <x-slot name="badge">
                        {{ filled($this->activePresetView) || filled($this->activeUserView) }}
                    </x-slot>
                @endif
            </x-filament::button>
        @else
            <x-filament::icon-button 
                :icon="$icon"
                :color="$color"
                :size="$size"
                class="h-9 w-9 advanced-tables-show-view-manager"
            >
                @if (Archilex\AdvancedTables\Support\Config::hasViewManagerBadge())
                    <x-slot name="badge">
                        {{ filled($this->activePresetView) || filled($this->activeUserView) }}
                    </x-slot>
                @endif
            </x-filament::icon-button>
        @endif   
    </x-slot>
    
    <div class="p-6">
        <x-advanced-tables::view-manager />
    </div>
</x-filament::dropdown>