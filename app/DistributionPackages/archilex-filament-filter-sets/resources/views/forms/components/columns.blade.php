@php
    use Archilex\AdvancedTables\Support\Config;

    $columns = $getChildComponentContainer()->getFlatFields();
    $formColumns = $this->getTable()->getColumnToggleFormColumns();
    $shouldAlwaysDisplayHiddenLabel = Config::reorderableColumnsShouldAlwaysDisplayHiddenLabel();
    $hasColumnsLayout = $this->getTable()->hasColumnsLayout();
    $hasContentGrid = $this->getTable()->getContentGrid();

    $visibleColumns = [];
    $hiddenColumns = [];

    $reorderIcon = Config::getReorderIcon();
    $checkMarkIcon = Config::getCheckMarkIcon();
    $dragHandleIcon = Config::getDragHandleIcon();
    $visibleIcon = Config::getVisibleIcon();
    $hiddenIcon = Config::getHiddenIcon();

    foreach ($columns as $key => $column) {
        ! $this->isTableColumnToggledHidden($key)
            ? $visibleColumns[] = $column
            : $hiddenColumns[] = $column;
    }
@endphp
<div
    x-data="{ 
        reordering: false,
    }"
    class="flex flex-col gap-y-4 text-sm"
>
    <div 
        @class([
            'flex flex-col gap-y-6',
            'grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6' => $formColumns === 2
        ])
        @style([
            '--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));'
        ])
    >
        <div class="flex flex-col">
            <div class="flex items-center justify-between min-h-[36px]">
                <h3 class="font-medium text-gray-400 dark:text-gray-500">
                    {{ __('advanced-tables::advanced-tables.toggled_columns.visible') }}
                </h3>
                <div>
                    @if (! ($hasColumnsLayout || $hasContentGrid))
                        <x-filament::icon-button
                            x-show="! reordering"
                            x-on:click="reordering = true"
                            :icon="$reorderIcon"
                            color="gray"
                        />
                    @endif
                    <x-filament::icon-button
                        x-show="reordering"
                        x-on:click="reordering = false"
                        :icon="$checkMarkIcon"
                        color="gray"
                    />
                </div>
            </div>

            <div
                x-on:end.stop="$wire.reorderTableColumns($event.target.sortable.toArray())"
                x-sortable
                class="flex flex-col gap-y-1"
            >
                @php
                    $uniqid = uniqid();
                @endphp

                @foreach ($visibleColumns as $column)
                    <div
                        wire:key="{{ $this->getId() }}.advanced-tables.toggled-columns.{{ $column->getName() }}.{{ $uniqid }}"
                        x-bind:x-sortable-item="reordering ? @js($column->getName()) : false"
                        x-sortable-handle
                        x-bind:class="{ 
                            'hover:bg-gray-100 dark:hover:bg-white/5 hover:rounded-lg cursor-move': reordering
                        }"
                        class="flex items-center justify-between px-3 py-1 -mx-3 gap-x-3"
                    >
                        <div class="flex flex-1 h-9 items-center font-medium text-gray-950 dark:text-white">
                            {{ $column->getLabel() }}
                        </div>

                        <div 
                            x-show="! reordering"
                        >
                            @if ($column->isDisabled())
                                <div class="-m-2 h-9 w-9 flex items-center justify-center">
                                    <x-filament::icon
                                        :icon="$visibleIcon"
                                        class="h-5 w-5 text-gray-400 dark:text-gray-500"
                                    />
                                </div>
                            @else
                                <x-filament::loading-indicator 
                                    wire:loading
                                    wire:target="toggledTableColumns.{{ $column->getName() }}"
                                    class="w-5 h-5"
                                />
                                <div
                                    wire:loading.remove
                                    wire:target="toggledTableColumns.{{ $column->getName() }}"
                                >
                                    <x-filament::icon-button
                                        wire:click="$toggle('toggledTableColumns.{{ $column->getName() }}')"
                                        :icon="$visibleIcon"
                                    />
                                </div>
                            @endif
                        </div>
                        
                        <div 
                            x-show="reordering"
                            class="-m-2 h-9 w-9 flex items-center justify-center"
                        >
                            <x-filament::icon
                                :icon="$dragHandleIcon"
                                class="h-5 w-5 text-gray-400 dark:text-gray-500"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        @if (count($hiddenColumns) || $shouldAlwaysDisplayHiddenLabel || $formColumns === 2)
            <div class="flex flex-col">
                <div class="flex items-center justify-between min-h-[36px]">
                    <h3 class="font-medium text-gray-400 dark:text-gray-500">
                        {{ __('advanced-tables::advanced-tables.toggled_columns.hidden') }}
                    </h3>
                </div>

                <div class="flex flex-col gap-y-1">
                    @foreach ($hiddenColumns as $column)
                        <div
                            wire:key="{{ $this->getId() }}.advanced-tables.toggled-columns.{{ $column->getName() }}"
                            class="flex items-center justify-between px-3 py-1 -mx-3 gap-x-3"
                        >
                            <div class="flex flex-1 h-9 items-center font-medium text-gray-950 dark:text-white">
                                {{ $column->getLabel() }}
                            </div>

                            <div>
                                <x-filament::loading-indicator 
                                    wire:loading
                                    wire:target="toggledTableColumns.{{ $column->getName() }}"
                                    class="w-5 h-5"
                                />
                                <div
                                    wire:loading.remove
                                    wire:target="toggledTableColumns.{{ $column->getName() }}"
                                >
                                    <x-filament::icon-button
                                        wire:click="$toggle('toggledTableColumns.{{ $column->getName() }}')"
                                        :icon="$hiddenIcon"
                                        color="gray"
                                    />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
