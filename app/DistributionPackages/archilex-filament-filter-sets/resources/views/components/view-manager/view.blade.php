@props([
    'canBeManaged' => false,
    'canClickToApply' => Archilex\AdvancedTables\Support\Config::canClickToApply(),
    'hasUserViews' => true,
    'isFavorite' => false,
    'viewGroup' => null, // for reordering
    'views' => null, // for reordering
    'view',
    'viewKey' => null,
    'viewType',
])

@php    
    $viewKey = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? $viewKey
        : $view->id;
    
    $label = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? $view->getLabel() ?? $this->generatePresetViewLabel($viewKey)
        : $view->getLabel();

    $isActive = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? $this->activePresetView == $viewKey
        : $this->activeUserView == $view->id;

    if ($view instanceOf \Archilex\AdvancedTables\Components\PresetView) {
        $sortableKey = $view->getManagedByCurrentUserId() ?: 'new_managed_preset_view_' . $viewKey;
    } else {
        $sortableKey = $isFavorite
            ? $view->managed_by_current_user_id ?? 'new_managed_global_view_' . $viewKey
            : $view->id;
    }

    $canBeManaged = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? $canBeManaged
        : $view->managed_by_current_user_id || (! $view->managed_by_current_user_id && Archilex\AdvancedTables\Support\Config::canManageGlobalUserViews());

    $filters = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? null
        : $view->filters;

    $visibility = $view instanceOf \Archilex\AdvancedTables\Components\PresetView
        ? null
        : ($view->isGlobal() ? 'global' : ($view->isPublic() ? 'public' : null));

    if ($view instanceOf \Archilex\AdvancedTables\Components\PresetView) {
        $actions = $this->getPresetViewActions(
            viewKey: $viewKey, 
            isFavorite: $isFavorite, 
            canBeManaged: $canBeManaged
        );
    } elseif ($view instanceOf \Archilex\AdvancedTables\Models\UserView) {
        $actions = $this->getUserViewActions(
            viewKey: $viewKey, 
            isFavorite: $isFavorite, 
            canBeManaged: $canBeManaged, 
            belongsToCurrentUser: $view->belongsToCurrentUser(), 
            filters: $view->filters, 
            visibility: $visibility
        );
    }
@endphp

<div
    @if (Archilex\AdvancedTables\Support\Config::hasSearchInViewManager())
        x-data="{
            label: @js($label)
        }"
        x-show="
            ! search || 
            label
                .replace(/ /g, '')
                .toLowerCase()
                .includes(search.replace(/ /g, '').toLowerCase())
        "  
    @endif
    wire:key="{{ $this->getId() }}.advanced-tables.{{ $views }}.{{ $viewKey }}"
    @if ($canBeManaged)
        x-bind:x-sortable-item="reorderViewGroup === @js($viewGroup) ? @js($sortableKey) : false"
        x-sortable-handle
    @endif
    x-bind:class="{ 
        'opacity-50': reorderingViews && reorderingViews !== @js($views),
        'cursor-move': reorderViewGroup === @js($viewGroup) && @js($canBeManaged),
        'cursor-pointer': reorderViewGroup !== @js($viewGroup) && @js($canClickToApply),
        'hover:bg-gray-100 dark:hover:bg-white/5 hover:rounded-lg': (reorderViewGroup === @js($viewGroup) && @js($canBeManaged)) || (@js($canClickToApply) && reorderViewGroup !== @js($viewGroup))
    }"
    @class([
        'flex items-center justify-between px-3 py-1 -mx-3 gap-x-3',
    ])
>
    <div 
        @if ($canClickToApply)
            x-on:click="
                reorderViewGroup !== @js($viewGroup)
                    ? $wire.call('load' + @js($viewType), @js($viewKey), @js($filters))
                    : null;
            "
        @endif   
        class="flex flex-1 h-9 items-center truncate"
    >
        <x-advanced-tables::view-manager.label
            :canBeReordered="$canBeManaged"
            :isActive="$isActive"
            :label="$label"
            :view="$view"
        />
    </div>
    
    @if ($canBeManaged)
        <div 
            x-show="reorderViewGroup === @js($viewGroup)"
            class="-m-2 h-9 w-9 flex items-center justify-center"
        >
            <x-filament::icon
                icon="heroicon-o-bars-2"
                class="h-5 w-5 text-gray-400 dark:text-gray-500"
            />
        </div>
    @endif

    @if ($actions || $hasUserViews)
        <div 
            x-show="@js(! $canBeManaged) || ! reorderViewGroup || reorderViewGroup !== @js($viewGroup)"
        >        
            <x-filament-actions::group 
                :actions="$actions"
                label="Actions"
                icon="heroicon-m-ellipsis-vertical"
                color="primary"
                size="md"
                dropdown-placement="bottom-end"
            />
        </div>
    @endif
</div>