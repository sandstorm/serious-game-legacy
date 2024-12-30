<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait CanReorderColumns
{
    public static function reorderableColumnsShouldAlwaysDisplayHiddenLabel(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->reorderableColumnsShouldAlwaysDisplayHiddenLabel();
        }

        return config('advanced-tables.reorderable_columns.always_display_hidden_label', false);
    }

    public static function reorderableColumnsAreEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->reorderableColumnsAreEnabled();
        }

        return config('advanced-tables.reorderable_columns.enabled', true);
    }

    public static function getReorderIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getReorderIcon();
        }

        return config('advanced-tables.reorderable_columns.reorder_icon', 'heroicon-m-arrows-up-down');
    }

    public static function getCheckMarkIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getCheckMarkIcon();
        }

        return config('advanced-tables.reorderable_columns.check_mark_icon', 'heroicon-m-check');
    }

    public static function getDragHandleIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getDragHandleIcon();
        }

        return config('advanced-tables.reorderable_columns.drag_handle_icon', 'heroicon-o-bars-2');
    }

    public static function getVisibleIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getVisibleIcon();
        }

        return config('advanced-tables.reorderable_columns.visible_icon', 'heroicon-s-eye');
    }

    public static function getHiddenIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getHiddenIcon();
        }

        return config('advanced-tables.reorderable_columns.hidden_icon', 'heroicon-o-eye-slash');
    }
}
