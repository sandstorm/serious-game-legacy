<div>
    <div
        x-data="{
            newSelectedRecords: [],
            previousSelectedRecords: [],
        }"
        x-init="
            $refs.modalContainer.addEventListener('modal-closed', () => {
                // This hook can be used to run any code on the modal close event
                // However, the current Livewire `RecordFinderTable` component
                // is already removed from the DOM, so we can't do anything
                // with `$wire` anymore ('L. component not found in DOM')
                
                // The `$el.remove()` ensures that the current Alpine
                // element is removed on time from DOM, so Alpine is
                // not trying to entangle again with child table.
                $el.remove()
            })
        
            newSelectedRecords = @Js($state);
            previousSelectedRecords = @Js($state);
            
            $watch('newSelectedRecords', (value) => {
                @if(! $isMultiple)
                if (value.length > 1 && value.length > previousSelectedRecords.length) {
                    const newSelectedRecord = value.filter((selectedRecord) => ! previousSelectedRecords.includes(selectedRecord))[0]
                    
                    newSelectedRecords = [newSelectedRecord]
                    
                    previousSelectedRecords = newSelectedRecords;
                    
                    return;
                }
                @endif
                
                $dispatch('selected-records-updated', { selectedRecords: value })
                
                previousSelectedRecords = value;
            })
        "
        x-on:selected-records-state-updated.window="
            if (newSelectedRecords.length === 0 && $event.detail.selectedRecords.length === 0) {
                return;
            }
            
            // Dispatch event for when state changes from within PHP...
            if (newSelectedRecords.length !== $event.detail.selectedRecords.length) {
                previousSelectedRecords = newSelectedRecords
                newSelectedRecords = $event.detail.selectedRecords
                
                if (newSelectedRecords.length === 0) {
                    $wire.resetTable()
                }
                
                return;
            }
            
            let diff = $event.detail.selectedRecords.filter(selectedRecord => ! newSelectedRecords.includes(selectedRecord));
            
            if (diff.length > 0) {
                previousSelectedRecords = newSelectedRecords
                newSelectedRecords = $event.detail.selectedRecords
                
                $dispatch('$refresh')
            }
        "
        @class([
            '[&_thead_.fi-ta-cell:first-child_.fi-checkbox-input]:hidden [&_.fi-ta-selection-indicator]:hidden [&_.fi-ta-row>.fi-ta-cell.bg-gray-50_.fi-checkbox-input]:hidden' => ! $isMultiple,
        ])
        {{--
            Putting a `wire:replace.self` on this element also `wire:replace`'s the action
            modals, which then cause a cannot call `dispatchEvent()` on null error when
            opening table actions. Therefore it's put purely on the table contents.
        --}}
    >
        {{--
            We will model the `x-data` to include an `x-modalable` that will bind the sync with the current component. We will remove
            the `x-ignore` in order to let the original component do it's Alpine syncing immediately (no idea why it's there in the
            first place), and then we *know* for sure that the `$watch()` provided above on the `x-modelable` can hook in on time.
            
            We will call `wire:replace.self` on the table container element to ensure that Livewire morphing does not morph this
            container with the table contents but will rip it out entirely and give it an entirely fresh instantiation every time.
            That solves issues with the table not being interactable because of not found Livewire components. In addition, by
            putting the `wire:replace.self` on the .fi-ta-ctn and not the entire `$this->table` contents ensures that the
            `<x-filament-actions::modals />` will *not* contain the `wire:replace.self` and be ripped out on every
            request. That would cause an issue that table action modals were not openable anymore due to morph.
            
            Note 09/11/2024: the `wire:replace.self` has been removed as that caused the Alpine state in
            the table to be lost on every refresh (like filter change). I have insteaad replaced the
            fixed `->key()` on the Livewire component with a dynamic `Str::random()` key instead.
        --}}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\RalphJSmit\Filament\RecordFinder\FilamentRecordFinder::RENDER_HOOK_RECORD_FINDER_TABLE_BEFORE) }}
        
        {{
            str($this->table->render())
                ->replace('x-data="table"', 'x-data="table" x-modelable="selectedRecords" x-model="newSelectedRecords"')
                ->replace('x-data="tableComponent"', 'x-data="tableComponent" x-modelable="selectedRecords" x-model="newSelectedRecords"')
                ->toHtmlString()
        }}
        
        {{ \Filament\Support\Facades\FilamentView::renderHook(\RalphJSmit\Filament\RecordFinder\FilamentRecordFinder::RENDER_HOOK_RECORD_FINDER_TABLE_AFTER) }}
    </div>
</div>