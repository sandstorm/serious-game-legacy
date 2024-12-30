@props([
    'canBeReordered' => true,
    'isActive' => false,
    'label' => null,
    'view' => null,
])

@php
    use Archilex\AdvancedTables\Support\Config;

    $isUserView = $view instanceOf \Archilex\AdvancedTables\Models\UserView;
    $color = $view->getColor();
    $icon = $view->getIcon() ?: Config::getDefaultViewIcon();
    $belongsToCurrentUser = $isUserView ? $view->belongsToCurrentUser() : false;
    $isPublic = $isUserView ? $view->isPublic() : false;
    $isGlobal = $isUserView ? $view->isGlobal() : false;
    $hasViewTypeBadges = Config::hasViewTypeBadges();
    $hasViewTypeIcons = Config::hasViewTypeIcons();
    $hasPublicIndicatorWhenGlobal = Config::hasPublicIndicatorWhenGlobal();
    $hasActiveViewBadge = Config::hasActiveViewBadge();
    $hasActiveViewIndicator = Config::hasActiveViewIndicator();
    $showViewIcon = Config::showViewIcon();
@endphp

<div class="flex w-full items-center gap-x-3 truncate">
    @if ($showViewIcon)
        <x-filament::icon
            :icon="$icon"
            class="flex-shrink-0 h-5 w-5 text-gray-500 dark:text-gray-400"
        />
    @endif
    
    <div 
        @class([
            'flex w-full gap-x-2 truncate justify-between',
            'items-center' => ! $hasViewTypeBadges || $hasViewTypeIcons || ! $isUserView,
            'items-baseline' => $hasViewTypeBadges && $isUserView,
        ])
    >
        <div 
            @class([
                'flex items-center gap-x-2 truncate',
                'font-medium text-custom-500 dark:text-custom-400' => filled($color),
            ])
            @style([
                \Filament\Support\get_color_css_variables($color, shades: [400, 500]) => filled($color),
            ])
        >
            <span class="truncate">
                {{ $label }}
            </span>
            @if ($hasActiveViewIndicator && $isActive)
                <span class="text-primary-500">&#8226;</span>
            @endif
        </div>
        
        <div class="flex flex-shrink-0 gap-x-1">
            @if ($hasActiveViewBadge && $isActive)
                <x-filament::badge 
                    color="primary"
                    size="sm"
                    class="flex-shrink-0"
                >
                    {{ __('advanced-tables::advanced-tables.view_manager.badges.active') }}
                </x-filament::badge>
            @endif
            @if ($hasViewTypeBadges)
                @if ($belongsToCurrentUser)
                    <x-filament::badge 
                        color="primary"
                        size="sm"
                        class="flex-shrink-0"
                    >
                        {{ __('advanced-tables::advanced-tables.view_manager.badges.user') }}
                    </x-filament::badge>
                @endif
                @if ($isGlobal)
                    <x-filament::badge 
                        color="info"
                        size="sm"
                        class="flex-shrink-0"
                    >
                        {{ __('advanced-tables::advanced-tables.view_manager.badges.global') }}
                    </x-filament::badge>
                @endif
                @if (($isPublic && ! $isGlobal) || ($isPublic && $isGlobal && $hasPublicIndicatorWhenGlobal))
                    <x-filament::badge 
                        color="success"
                        size="sm"
                        class="flex-shrink-0"
                    >
                        {{ __('advanced-tables::advanced-tables.view_manager.badges.public') }}
                    </x-filament::badge>
                @endif
                @if (! $isUserView)
                    <x-filament::badge 
                        color="gray"
                        size="sm"
                        class="flex-shrink-0"
                    >
                        {{ __('advanced-tables::advanced-tables.view_manager.badges.preset') }}
                    </x-filament::badge>
                @endif
            @endif

            @if ($hasViewTypeIcons)
                @if ($belongsToCurrentUser)
                    <x-filament::icon
                        icon="heroicon-o-user"
                        class="flex-shrink-0 h-4 w-4 text-gray-300 dark:text-gray-600"
                    />
                @endif
                @if ($isGlobal)
                    <x-filament::icon
                        icon="heroicon-o-globe-alt"
                        class="flex-shrink-0 h-4 w-4 text-gray-300 dark:text-gray-600"
                    />
                @endif
                @if (($isPublic && ! $isGlobal) || ($isPublic && $isGlobal && $hasPublicIndicatorWhenGlobal))
                    <x-filament::icon
                        icon="heroicon-o-eye"
                        class="flex-shrink-0 h-4 w-4 text-gray-300 dark:text-gray-600"
                    />
                @endif
                @if (! $isUserView)
                    <x-filament::icon
                        icon="heroicon-o-lock-closed"
                        class="flex-shrink-0 h-4 w-4 text-gray-300 dark:text-gray-600"
                    />
                @endif
            @endif
        </div>
    </div>
</div>