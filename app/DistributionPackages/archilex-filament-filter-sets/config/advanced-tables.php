<?php

return [
    /*
    --------------------------------------------------------------------------
    | Important
    --------------------------------------------------------------------------
    | These configurations are exclusively for use with Filament's standalone
    | Table Builder. If you are using Advanced Tables with Filament Panels, you
    | will need to configure the plugin directly in your panel.
    */

    /*
    |--------------------------------------------------------------------------
    | Favorites Bar
    |--------------------------------------------------------------------------
    */

    'favorites_bar' => [
        'enabled' => true,
        'theme' => Archilex\AdvancedTables\Enums\FavoritesBarTheme::Github,
        'default_icon' => 'heroicon-o-bars-4',
        'icon_position' => 'before',
        'size' => 'md',
        'default_view' => true,
        'divider' => true,
        'loading_indicator' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Builder
    |--------------------------------------------------------------------------
    */

    'filter_builder' => [
        'expand_view_styles' => ['right: 80px', 'top: 24px'],
    ],

    /*
    --------------------------------------------------------------------------
    | Managed User Views
    --------------------------------------------------------------------------
    */

    'managed_user_views' => [
        'managed_user_view' => Archilex\AdvancedTables\Models\ManagedUserView::class,
    ],

    /*
    --------------------------------------------------------------------------
    | Persist Active View To Session
    --------------------------------------------------------------------------
    */

    'persist_active_view_in_session' => false,

    /*
    --------------------------------------------------------------------------
    | Preset Views
    --------------------------------------------------------------------------
    */

    'preset_views' => [
        'create_using_preset_view' => true,
        'new_preset_view_sort_position' => 'before',
        'preset_views_manageable' => true,
        'lock_icon' => null,
        'managed_preset_view' => Archilex\AdvancedTables\Models\ManagedPresetView::class,
        'legacy_dropdown' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quick Save
    |--------------------------------------------------------------------------
    */

    'quick_save' => [
        'in_favorites_bar' => true,
        'in_table' => false,
        'position' => 'end',
        'table_position' => 'tables::toolbar.search.after',
        'slide_over' => true,
        'colors' => [
            'success',
            'info',
            'warning',
            'danger',
            'gray',
        ],
        'icon' => 'heroicon-o-plus',
        'name_helper_text' => false,
        'filters_helper_text' => false,
        'public_helper_text' => true,
        'favorite_helper_text' => true,
        'global_helper_text' => true,
        'active_preset_view_helper_text' => true,
        'icon_select' => true,
        'include_outline_icons' => true,
        'include_solid_icons' => true,
        'add_to_favorites' => true,
        'make_public' => true,
        'make_global_favorite' => false,
        'color_picker' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reorderable Columns
    |--------------------------------------------------------------------------
    */

    'reorderable_columns' => [
        'always_display_hidden_columns_label' => false,
        'enabled' => true,
        'reorder_icon' => 'heroicon-m-arrows-up-down',
        'check_mark_icon' => 'heroicon-m-check',
        'drag_handle_icon' => 'heroicon-o-bars-2',
        'visible_icon' => 'heroicon-s-eye',
        'hidden_icon' => 'heroicon-o-eye-slash',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    'status' => [
        'minimum_status' => Archilex\AdvancedTables\Enums\Status::Pending,
        'initial_status' => Archilex\AdvancedTables\Enums\Status::Pending,
    ],

    /*
    --------------------------------------------------------------------------
    | Support
    --------------------------------------------------------------------------
    */

    'support' => [
        'convert_icons' => false,
    ],

    /*
    --------------------------------------------------------------------------
    | Tenancy
    --------------------------------------------------------------------------
    */

    'tenancy' => [
        'enabled' => true,
        'tenant' => null,
        'tenant_column' => 'tenant_id',
    ],

    /*
    --------------------------------------------------------------------------
    | Users
    --------------------------------------------------------------------------
    */

    'users' => [
        'user' => App\Models\User::class,
        'user_table' => 'users',
        'user_table_key_column' => 'id',
        'user_table_name_column' => 'name',
        'auth_guard' => null,
    ],

    /*
    --------------------------------------------------------------------------
    | User Views
    --------------------------------------------------------------------------
    */

    'user_views' => [
        'enabled' => true,
        'global_user_views_manageable' => true,
        'new_global_user_view_sort_position' => 'before',
        'user_view' => Archilex\AdvancedTables\Models\UserView::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | User View Resource
    |--------------------------------------------------------------------------
    */

    'user_view_resource' => [
        'navigation_badge' => true,
        'navigation_icon' => 'heroicon-o-funnel',
        'navigation_group' => null,
        'navigation_sort' => null,
        'loads_all_users' => true,
        'panels' => null,
    ],

    /*
    --------------------------------------------------------------------------
    | View Manager
    --------------------------------------------------------------------------
    */

    'view_manager' => [
        'in_favorites_bar' => true,
        'in_table' => false,
        'position' => 'end',
        'table_position' => 'tables::toolbar.search.after',
        'slide_over' => false,
        'button' => false,
        'button_size' => 'md',
        'button_label' => 'Views',
        'button_outlined' => false,
        'save' => false,
        'reset' => false,
        'search' => true,
        'icon' => 'heroicon-o-queue-list',
        'icon_position' => 'before',
        'badge' => true,
        'click_to_apply' => true,
        'apply_button' => true,
        'view_type_badges' => false,
        'view_type_icons' => true,
        'public_indicator_when_global' => false,
        'active_view_badge' => false,
        'active_view_indicator' => true,
        'view_icon' => true,
        'default_view_icon' => 'heroicon-o-funnel',
    ],
];
