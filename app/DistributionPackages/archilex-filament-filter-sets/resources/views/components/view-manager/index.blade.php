@php
    use Archilex\AdvancedTables\Support\Config;
    
    $userViewsAreEnabled = Config::userViewsAreEnabled();
    $presetViewsCanBeManaged = Config::canManagePresetViews();
    $isViewManagerInFavoritesBar = Config::isViewManagerInFavoritesBar();
    $showViewManagerAsSlideOver = Config::showViewManagerAsSlideOver();
    $hasSaveInViewManager = Config::hasSaveInViewManager();
    $hasResetInViewManager = Config::hasResetInViewManager();
    $hasSearchInViewManager = Config::hasSearchInViewManager();
    $isViewManagerInTable = Config::isViewManagerInTable();

    $userViews = $this->getUserViewsArray();
    $presetViews = $this->getPresetViewsArray();
    $hasUserViews = collect($userViews)->filter(fn ($userView) => filled($userView))->isNotEmpty();
    $showUserFavoriteViewsReorderButton = 
        ($presetViews['favoritePresetViews']->isNotEmpty() && $presetViewsCanBeManaged) || 
        $userViews['favoriteUserViews']
            ->filter(fn ($userView) => 
                $userView->managed_by_current_user_id || 
                (
                    ! $userView->managed_by_current_user_id && 
                    Config::canManageGlobalUserViews()
                )
            )->isNotEmpty();
@endphp

<div 
    x-data="{
        reorderViewGroup: null,
        reorderingViews: null,
        search: null
    }"
    class="flex flex-col gap-y-4 text-sm"
>
    @if (! $showViewManagerAsSlideOver)
        <div class="flex items-center justify-between">
            <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ __('advanced-tables::advanced-tables.view_manager.table_heading') }}
            </h4>
    
            <div class="flex items-center gap-x-4">
                @if ($userViewsAreEnabled && $hasSaveInViewManager)
                    {{ ($action = $this->saveUserViewAction())->isVisible() ? $action : null }}
                @endif
    
                @if ($userViewsAreEnabled && $hasResetInViewManager)
                    <x-filament::link
                        color="danger"
                        tag="button"
                        wire:click="resetTableToDefault"
                    >
                        {{ __('filament-tables::table.filters.actions.reset.label') }}
                    </x-filament::link>
                @endif
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-y-6">
        @if ($hasSearchInViewManager)
            <div
                x-id="['input']"
            >
                <label x-bind:for="$id('input')" class="sr-only">
                    {{ __('filament-tables::table.fields.search.label') }}
                </label>

                <x-filament::input.wrapper
                    inline-prefix
                    prefix-icon="heroicon-m-magnifying-glass"
                    prefix-icon-alias="tables::search-field"
                >
                    <x-filament::input
                        x-model="search"
                        autocomplete="off"
                        inline-prefix
                        :placeholder="__('filament-tables::table.fields.search.placeholder')"
                        type="search"
                        x-bind:id="$id('input')"
                    />
                </x-filament::input.wrapper>
            </div>
        @endif

        @if (! count($presetViews['favoritePresetViews']) && ! count($presetViews['hiddenPresetViews']) && ! $hasUserViews)
            <div class="text-center text-gray-400 dark:text-gray-500 py-3">
                {{ __('advanced-tables::advanced-tables.view_manager.no_views') }}
            </div>
        @endif

        {{-- User favorites --}}
        @if (count($presetViews['favoritePresetViews']) || count($userViews['favoriteUserViews']))
            <x-advanced-tables::view-manager.view-container
                canBeReordered="{{ $showUserFavoriteViewsReorderButton }}"
                viewGroup="userFavorites"
                viewGroupLabel="user_favorites"
            >                    
                {{-- Favorite preset views --}}
                <x-advanced-tables::view-manager.view-group
                    :canBeManaged=true
                    model="managedPresetView"
                    views="favoritePresetViews"
                >
                    @foreach ($presetViews['favoritePresetViews'] as $presetViewName => $presetView)                
                        <x-advanced-tables::view-manager.view
                            :canBeManaged="$presetViewsCanBeManaged"
                            :isFavorite=true
                            :hasUserViews="$hasUserViews"
                            :view="$presetView"
                            :viewKey="$presetViewName"
                            viewGroup="userFavorites"
                            views="favoritePresetViews"
                            viewType="PresetView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>

                {{-- Favorite user views --}}
                <x-advanced-tables::view-manager.view-group
                    :canBeManaged=true
                    model="managedUserView"
                    views="favoriteUserViews"
                >
                    @foreach ($userViews['favoriteUserViews'] as $userView)
                        <x-advanced-tables::view-manager.view
                            :isFavorite=true
                            :view="$userView"
                            viewGroup="userFavorites"
                            views="favoriteUserViews"
                            viewType="UserView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>
            </x-advanced-tables::view-manager.view-container>
        @endif

        {{-- Hidden user views --}}
        @if (count($userViews['hiddenUserViews']))
            <x-advanced-tables::view-manager.view-container
                :canBeReordered=true
                viewGroup="hiddenUserViews"
                viewGroupLabel="user_views"
            >
                <x-advanced-tables::view-manager.view-group
                :canBeManaged=true
                :isFavorite=false
                model="userView"
                views="hiddenUserViews"
                >
                    @foreach ($userViews['hiddenUserViews'] as $userView)
                        <x-advanced-tables::view-manager.view
                            :isFavorite=false
                            :view="$userView"
                            viewGroup="hiddenUserViews"
                            views="hiddenUserViews"
                            viewType="UserView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>
            </x-advanced-tables::view-manager.view-container>
        @endif
        
        {{-- Hidden preset views --}}
        @if (count($presetViews['hiddenPresetViews']))
            <x-advanced-tables::view-manager.view-container
                canBeReordered="{{ $presetViewsCanBeManaged }}"
                viewGroup="hiddenPresetViews"
                viewGroupLabel="preset_views"
            >
                <x-advanced-tables::view-manager.view-group
                    :canBeManaged="$presetViewsCanBeManaged"
                    :isFavorite=false
                    model="managedPresetView"
                    views="hiddenPresetViews"
                >
                    @foreach ($presetViews['hiddenPresetViews'] as $presetViewName => $presetView)                    
                        <x-advanced-tables::view-manager.view
                            :canBeManaged="$presetViewsCanBeManaged"
                            :isFavorite=false
                            viewGroup="hiddenPresetViews"
                            views="hiddenPresetViews"
                            :hasUserViews="$hasUserViews"
                            :view="$presetView"
                            :viewKey="$presetViewName"
                            viewType="PresetView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>
            </x-advanced-tables::view-manager.view-container>
        @endif

        {{-- Global user views --}}
        @if (count($userViews['globalUserViews']))
            <x-advanced-tables::view-manager.view-container
                viewGroup="globalUserViews"    
                viewGroupLabel="global_views"
            >
                <x-advanced-tables::view-manager.view-group>
                    @foreach ($userViews['globalUserViews'] as $userView)
                        <x-advanced-tables::view-manager.view
                            :isFavorite=false
                            viewGroup="globalUserViews"
                            :hasUserViews="count($userViews)"
                            :view="$userView"
                            viewType="UserView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>
            </x-advanced-tables::view-manager.view-container>
        @endif

        {{-- Public user views --}}
        @if (count($userViews['publicUserViews']))
            <x-advanced-tables::view-manager.view-container
                viewGroup="publicUserViews"
                viewGroupLabel="public_views"
            >
                <x-advanced-tables::view-manager.view-group>
                    @foreach ($userViews['publicUserViews'] as $userView)
                        <x-advanced-tables::view-manager.view
                            :isFavorite=false
                            viewGroup="publicUserViews"
                            :hasUserViews="count($userViews)"
                            :view="$userView"
                            viewType="UserView"
                        />
                    @endforeach
                </x-advanced-tables::view-manager.view-group>
            </x-advanced-tables::view-manager.view-container>
        @endif
    </div>
</div>