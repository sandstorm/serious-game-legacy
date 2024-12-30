<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasQuickSave
{
    public static function isQuickSaveInFavoritesBar(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->isQuickSaveInFavoritesBar();
        }

        return config('advanced-tables.quick_save.in_favorites_bar', true);
    }

    public static function isQuickSaveInTable(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->isQuickSaveInTable();
        }

        return config('advanced-tables.quick_save.in_table', false);
    }

    public static function quickSavePosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->quickSavePosition();
        }

        return config('advanced-tables.quick_save.position', 'end');
    }

    public static function quickSaveTablePosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->quickSaveTablePosition();
        }

        return config('advanced-tables.quick_save.table_position', 'tables::toolbar.search.after');
    }

    public static function showQuickSaveAsSlideOver(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showQuickSaveAsSlideOver();
        }

        return config('advanced-tables.quick_save.slide_over', true);
    }

    public static function getQuickSaveColors(): array
    {
        $defaultColors = [
            'success',
            'info',
            'warning',
            'danger',
            'gray',
        ];

        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getQuickSaveColors() ?? $defaultColors;
        }

        return config('advanced-tables.quick_save.colors', $defaultColors);
    }

    public static function getQuickSaveIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getQuickSaveIcon();
        }

        return config('advanced-tables.quick_save.icon', 'heroicon-o-plus');
    }

    public static function hasQuickSaveNameHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveNameHelperText();
        }

        return config('advanced-tables.quick_save.name_helper_text', false);
    }

    public static function hasQuickSaveFiltersHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveFiltersHelperText();
        }

        return config('advanced-tables.quick_save.filters_helper_text', false);
    }

    public static function hasQuickSavePublicHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSavePublicHelperText();
        }

        return config('advanced-tables.quick_save.public_helper_text', true);
    }

    public static function hasQuickSaveFavoriteHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveFavoriteHelperText();
        }

        return config('advanced-tables.quick_save.favorite_helper_text', true);
    }

    public static function hasQuickSaveGlobalFavoriteHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveGlobalFavoriteHelperText();
        }

        return config('advanced-tables.quick_save.global_helper_text', true);
    }

    public static function hasQuickSaveActivePresetViewHelperText(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveActivePresetViewHelperText();
        }

        return config('advanced-tables.quick_save.active_preset_view_helper_text', true);
    }

    public static function hasQuickSaveIconSelect(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveIconSelect();
        }

        return config('advanced-tables.quick_save.icon_select', true);
    }

    public static function includesOutlineIcons(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->includesOutlineIcons();
        }

        return config('advanced-tables.quick_save.include_outline_icons', true);
    }

    public static function includesSolidIcons(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->includesSolidIcons();
        }

        return config('advanced-tables.quick_save.include_solid_icons', false);
    }

    public static function hasQuickSaveMakeFavorite(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveMakeFavorite();
        }

        return config('advanced-tables.quick_save.add_to_favorites', true);
    }

    public static function hasQuickSaveMakePublic(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveMakePublic();
        }

        return config('advanced-tables.quick_save.make_public', true);
    }

    public static function hasQuickSaveMakeGlobalFavorite(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveMakeGlobalFavorite();
        }

        return config('advanced-tables.quick_save.make_global_favorite', false);
    }

    public static function hasQuickSaveColorPicker(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasQuickSaveColorPicker();
        }

        return config('advanced-tables.quick_save.color_picker', true);
    }
}
