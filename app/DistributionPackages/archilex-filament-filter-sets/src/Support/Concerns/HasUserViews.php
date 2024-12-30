<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Models\UserView;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasUserViews
{
    public static function canManageGlobalUserViews(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->canManageGlobalUserViews();
        }

        return config('advanced-tables.user_views.global_views_manageable', true);
    }

    public static function getNewGlobalUserViewSortPosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getNewGlobalUserViewSortPosition();
        }

        return config('advanced-tables.user_views.new_global_user_view_sort_position', 'before');
    }

    public static function getUserView(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getUserView();
        }

        return config('advanced-tables.user_views.user_view', UserView::class);
    }

    public static function userViewsAreEnabled(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->userViewsAreEnabled();
        }

        return config('advanced-tables.user_views.enabled', true);
    }
}
