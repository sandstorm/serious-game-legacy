@props([
    'form',
    'indicatorsCount' => null,
    'maxHeight' => null,
    'triggerAction',
    'width' => 'xs',
])

<div class="flex gap-x-2 items-center">
    
    @if (Archilex\AdvancedTables\Support\Config::isViewManagerInTable())
        <x-advanced-tables::view-manager.dropdown />
    @endif

    <x-filament::dropdown
        :max-height="$maxHeight"
        placement="bottom-end"
        shift
        :width="$width"
        wire:key="{{ $this->getId() }}.table.filters"
        {{ $attributes->class(['fi-ta-filters-dropdown']) }}
    >
        <x-slot name="trigger">
            <span
                @class([
                    'inline-flex',
                    '-mx-2' => $triggerAction->isIconButton(),
                ])
            >
                {{ $triggerAction->badge($indicatorsCount) }}
            </span>
        </x-slot>
    
        <x-filament-tables::filters :form="$form" class="p-6" />
    </x-filament::dropdown>
</div>
