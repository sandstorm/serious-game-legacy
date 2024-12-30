<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasResource
{
    public static function resourceLoadsAllUsers(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->resourceLoadsAllUsers();
        }

        return config('advanced-tables.user_view_resource.loads_all_users', true);
    }

    public static function hasResourceNavigationBadge(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasResourceNavigationBadge();
        }

        return config('advanced-tables.user_view_resource.navigation_badge', true);
    }

    public static function getResourceNavigationIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getResourceNavigationIcon();
        }

        return config('advanced-tables.user_view_resource.navigation_icon', 'heroicon-o-funnel');
    }

    public static function getResourceNavigationGroup(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getResourceNavigationGroup();
        }

        return config('advanced-tables.user_view_resource.navigation_group', null);
    }

    public static function getResourceNavigationSort(): ?int
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getResourceNavigationSort();
        }

        return config('advanced-tables.user_view_resource.navigation_sort', null);
    }

    public static function getResourcePanels(): ?array
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getResourcePanels();
        }

        return config('advanced-tables.user_view_resource.panels', null);
    }
}
