@php
    use Archilex\AdvancedTables\Support\Config;
    use Archilex\AdvancedTables\Enums\FavoritesBarTheme;
    
    $theme = match (Config::getFavoritesBarTheme()) {
        FavoritesBarTheme::Links, 'links' => FavoritesBarTheme::Links,
        FavoritesBarTheme::SimpleLinks, 'links-simple' => FavoritesBarTheme::SimpleLinks,
        FavoritesBarTheme::Tabs, 'tabs' => FavoritesBarTheme::Tabs,
        FavoritesBarTheme::BrandedTabs, 'tabs-brand' => FavoritesBarTheme::BrandedTabs,
        FavoritesBarTheme::Github, 'github' => FavoritesBarTheme::Github,
        FavoritesBarTheme::Filament, 'filament' => FavoritesBarTheme::Filament,
    };

    $displayDivider = Config::favoritesBarHasDivider();
    $hasDefaultView = $this->hasDefaultView();
    $defaultIcon = Config::getFavoritesBarDefaultIcon();
    $iconPosition = Config::getFavoritesBarIconPosition();
    $size = Config::getFavoritesBarSize();
    $isViewManagerInFavoritesBar = Config::isViewManagerInFavoritesBar();
    $isQuickSaveInFavoritesBar = Config::isQuickSaveInFavoritesBar();
    $showViewManagerAsSlideOver = Config::showViewManagerAsSlideOver();
    $viewManagerPosition = Config::viewManagerPosition();
    $quickSavePosition = Config::quickSavePosition();
    $userViewsAreEnabled = Config::userViewsAreEnabled();
    $hasPresetViewLegacyDropdown = Config::hasPresetViewLegacyDropdown();
    $favoritesBarHasLoadingIndicator = Config::favoritesBarHasLoadingIndicator();

    $isRelationManager = $this->isRelationManager() ?? false;
    $isTableWidget = $this->isTableWidget() ?? false;

    // When showing the view manager as a slideOver we only need to 
    // load the user's favorite views on boot to save memory. The 
    // remaining views are loaded when the slideOver is activated. 
    // However, when using the dropdown we need all the views on boot
    // so we switch methods to grab all the views and pull out
    // the favorites.
    $favoriteUserViews = $showViewManagerAsSlideOver
        ? $this->getFavoriteUserViews()
        : $this->getFavoriteUserViewsFromUserViews();

    if ($hasPresetViewLegacyDropdown || $showViewManagerAsSlideOver) {
        $mergedPresetViews = $this->getMergedPresetViews();
    }

    $favoritePresetViews = $showViewManagerAsSlideOver
        ? $this->buildFavoritePresetViewsFrom($mergedPresetViews)
        : $this->getFavoritePresetViewsFromPresetViews();
@endphp

<div 
    @class([
        'flex justify-between items-center',
        '-mb-3' => ! $isTableWidget && $theme !== FavoritesBarTheme::Github,
        '-mb-7' => ! $isTableWidget && $theme === FavoritesBarTheme::Github,
        'mb-3' => $isTableWidget && $theme !== FavoritesBarTheme::Github,
        '-mb-1' => $isTableWidget && $theme === FavoritesBarTheme::Github,
    ])
>
    @if (count($favoritePresetViews) || count($favoriteUserViews) || $isQuickSaveInFavoritesBar || $isViewManagerInFavoritesBar)    
        <div
            @class([
                'advanced-tables-fav-bar-container w-full items-center sm:space-y-0',
                '-mb-0.5' => $theme === FavoritesBarTheme::Github,
                '-mb-3' => $theme !== FavoritesBarTheme::Github && $theme !== FavoritesBarTheme::Filament,
                'space-y-4 sm:flex' => $theme !== FavoritesBarTheme::Filament,
                'flex rounded-xl bg-white ps-2 pe-4 sm:pe-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => $theme === FavoritesBarTheme::Filament,
            ])
        >
            @if ($hasPresetViewLegacyDropdown)
                <div 
                    @class([
                        'advanced-tables-fav-bar-dropdown flex-shrink-0 sm:w-[12rem]',
                        'mb-2 sm:me-0.5' => $theme === FavoritesBarTheme::Github,
                        'sm:me-4' => $theme !== FavoritesBarTheme::Github,
                        'me-2' => $theme === FavoritesBarTheme::Filament,
                        'w-full' => $theme !== FavoritesBarTheme::Filament,
                    ])
                >
                    {{ $this->getPresetViewsForm($mergedPresetViews) }}
                </div>
            @endif
            
            <div @class([
                'flex flex-1 gap-x-4 overflow-hidden',
                'ps-2 sm:ps-3.5' => $theme === FavoritesBarTheme::Github,
                'h-14 pe-4 sm:pe-6' => $theme !== FavoritesBarTheme::Filament,
                'h-[52px] sm:pe-4' => $theme === FavoritesBarTheme::Filament,
            ])>
                @if (
                    ($userViewsAreEnabled && $isQuickSaveInFavoritesBar && $quickSavePosition === 'start') || 
                    ($isViewManagerInFavoritesBar && $viewManagerPosition === 'start')
                )
                    <div 
                        @class([
                            'flex items-center',
                            'mb-2' => $theme === FavoritesBarTheme::Github,
                        ])
                    >
    
                        @if ($userViewsAreEnabled && $isQuickSaveInFavoritesBar && $quickSavePosition === 'start')
                            {{ ($action = $this->saveUserViewAction())->isVisible() ? $action : null }}
                        @endif
                        
                        @if ($isViewManagerInFavoritesBar && $viewManagerPosition === 'start')
                            <x-advanced-tables::view-manager.button 
                                placement="bottom-start"
                            />
                        @endif
                    </div>
                @endif
                
                <nav
                    x-data="{ 
                        activeUserView: $wire.entangle('activeUserView'),
                        activePresetView: $wire.entangle('activePresetView'),
                        defaultViewIsActive: $wire.entangle('defaultViewIsActive'),
                    }"
                    @class([
                        'advanced-tables-fav-bar-nav flex flex-1 items-center overflow-x-auto',
                    ])
                    aria-label="Tabs"
                >
                    <ul 
                        @class([
                            'advanced-tables-fav-bar-list flex text-sm overflow-x-auto',
                            'gap-x-3 md:gap-x-5' => ($theme === FavoritesBarTheme::Links || $theme === FavoritesBarTheme::SimpleLinks) ,
                            'gap-x-2' => ($theme === FavoritesBarTheme::Tabs || $theme === FavoritesBarTheme::BrandedTabs || $theme === FavoritesBarTheme::Github),
                            'gap-x-1' => $theme === FavoritesBarTheme::Filament,
                        ])
                    >                    
                        @if ($hasDefaultView)
                            <x-advanced-tables::favorites-bar.button 
                                :theme="$theme"
                                :icon="$defaultIcon"
                                :iconPosition="$iconPosition"
                                :size="$size"
                                class="advanced-tables-fav-bar-all"
                            >
                                {{ __('advanced-tables::advanced-tables.tables.favorites.default') }}
                            </x-advanced-tables::favorites-bar.button>
                        @endif
    
                        @foreach ($favoritePresetViews as $presetViewName => $presetView)
                            <x-advanced-tables::favorites-bar.button 
                                :badge="$presetView->getBadge()"
                                :badgeColor="$presetView->getBadgeColor()"
                                :presetViewName="$presetViewName"
                                :theme="$theme"
                                :color="$presetView->getColor()"
                                :icon="$presetView->getIcon()"
                                :tooltip="$presetView->getTooltip()"
                                :iconPosition="$iconPosition"
                                :size="$size"
                                class="advanced-tables-fav-bar-dev-set"
                            >
                                {{ $presetView->getLabel() ?? $this->generatePresetViewLabel($presetViewName) }}
                            </x-advanced-tables::favorites-bar.button>
                        @endforeach
    
                        @if (count($favoritePresetViews) && count($favoriteUserViews) && $displayDivider)
                            <li 
                                @class([
                                    'advanced-tables-fav-bar-divider flex items-center',
                                    'mb-2' => $theme === FavoritesBarTheme::Github,
                                ])
                            >
                                <span class="border-e border-gray-300 h-6 dark:border-gray-700"></span>
                            </li>
                        @endif
    
                        @foreach ($favoriteUserViews as $userView)
                            <x-advanced-tables::favorites-bar.button 
                                :userView="$userView"
                                :theme="$theme"
                                :color="$userView->getColor()"
                                :icon="$userView->getIcon()"
                                :iconPosition="$iconPosition"
                                :size="$size"
                                class="advanced-tables-fav-bar-user-set"
                            >
                                {{ $userView->name }}
                            </x-advanced-tables::favorites-bar.button>
                        @endforeach
                    </ul>
                </nav>
                
                @if (
                    ($favoritesBarHasLoadingIndicator) ||
                    ($userViewsAreEnabled && $isQuickSaveInFavoritesBar && $quickSavePosition === 'end') ||
                    ($isViewManagerInFavoritesBar && $viewManagerPosition === 'end')
                )
                    <div 
                        @class([
                            'flex items-center gap-x-4',
                            'mb-2' => $theme === FavoritesBarTheme::Github,
                        ])
                    >
                        @if ($favoritesBarHasLoadingIndicator)
                            <x-filament::loading-indicator 
                                wire:loading
                                wire:target="resetTableToDefault, loadUserView, loadPresetView"
                                class="w-5 h-5"
                            />
                        @endif
                        
                        @if ($userViewsAreEnabled && $isQuickSaveInFavoritesBar && $quickSavePosition === 'end')
                            {{ ($action = $this->saveUserViewAction())->isVisible() ? $action : null }}
                        @endif
                        
                        @if ($isViewManagerInFavoritesBar && $viewManagerPosition === 'end')
                            <x-advanced-tables::view-manager.button />
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>