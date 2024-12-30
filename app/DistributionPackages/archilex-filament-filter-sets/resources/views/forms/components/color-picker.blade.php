@props([
    'actions',
    'color' => null,
    'label' => __('filament-support::actions/group.trigger.label'),
    'size' => 'md',
])

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-actions="$getHintActions()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}')
        }"
        class="flex flex-wrap items-center gap-2 mb-2"
    >    
        @foreach (Archilex\AdvancedTables\Support\Config::getQuickSaveColors() as $color)   
            <x-advanced-tables::color-button
                x-on:click="
                    state = (state !== '{{ $color }}')
                        ? '{{ $color }}'
                        : null
                "
                :color="$color"
                icon="heroicon-s-check"
                :size="$size"
            />
        @endforeach
    </div>
</x-dynamic-component>
