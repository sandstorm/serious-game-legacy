<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Models\ManagedPresetView;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasPresetViews
{
    public static function canCreateUsingPresetView(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->canCreateUsingPresetView();
        }

        return config('advanced-tables.preset_views.create_using_preset_view', true);
    }

    public static function canManagePresetViews(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->canManagePresetViews();
        }

        return config('advanced-tables.preset_views.preset_views_manageable', true);
    }

    public static function getManagedPresetView(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getManagedPresetView();
        }

        return config('advanced-tables.preset_views.managed_preset_view', ManagedPresetView::class);
    }

    public static function getPresetViewLockIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getPresetViewLockIcon();
        }

        return config('advanced-tables.preset_views.lock_icon');
    }

    public static function getNewPresetViewSortPosition(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getNewPresetViewSortPosition();
        }

        return config('advanced-tables.preset_views.new_preset_view_sort_position', 'before');
    }

    public static function hasPresetViewLegacyDropdown(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasPresetViewLegacyDropdown();
        }

        return config('advanced-tables.preset_views.legacy_dropdown', false);
    }
}
