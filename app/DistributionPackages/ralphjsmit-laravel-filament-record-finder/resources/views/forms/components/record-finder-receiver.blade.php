<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath = $getStatePath() }}')
        }"
        x-on:selected-records-updated.window="state = $event.detail.selectedRecords"
        x-init="
            $watch('state', (value) => {
                // Dispatch event for when state changes from within PHP...
                $dispatch('selected-records-state-updated', { selectedRecords: value ?? [] })
            })
        "
    >
    </div>
</x-dynamic-component>