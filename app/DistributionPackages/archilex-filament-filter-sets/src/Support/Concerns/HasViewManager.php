<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Support\Enums\IconPosition;

trait HasViewManager
{
    public static function isViewManagerInFavoritesBar(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->isViewManagerInFavoritesBar();
        }

        return config('advanced-tables.view_manager.in_favorites_bar', true);
    }

    public static function isViewManagerInTable(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->isViewManagerInTable();
        }

        return config('advanced-tables.view_manager.in_table', false);
    }

    public static function viewManagerPosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->viewManagerPosition();
        }

        return config('advanced-tables.view_manager.position', 'end');
    }

    public static function viewManagerTablePosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->viewManagerTablePosition();
        }

        return config('advanced-tables.view_manager.table_position', 'tables::toolbar.search.after');
    }

    public static function showViewManagerAsSlideOver(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showViewManagerAsSlideOver();
        }

        return config('advanced-tables.view_manager.slide_over', false);
    }

    public static function showViewManagerAsButton(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showViewManagerAsButton();
        }

        return config('advanced-tables.view_manager.button', false);
    }

    public static function getViewManagerButtonLabel(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getViewManagerButtonLabel();
        }

        return config('advanced-tables.view_manager.button_label', 'Views');
    }

    public static function getViewManagerButtonSize(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getViewManagerButtonSize();
        }

        return config('advanced-tables.view_manager.button_size', 'md');
    }

    public static function showViewManagerButtonOutlined(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showViewManagerButtonOutlined();
        }

        return config('advanced-tables.view_manager.button_outlined', false);
    }

    public static function hasSaveInViewManager(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasSaveInViewManager();
        }

        return config('advanced-tables.view_manager.save', false);
    }

    public static function hasResetInViewManager(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasResetInViewManager();
        }

        return config('advanced-tables.view_manager.reset', false);
    }

    public static function hasSearchInViewManager(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasSearchInViewManager();
        }

        return config('advanced-tables.view_manager.search', true);
    }

    public static function getViewManagerIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getViewManagerIcon();
        }

        return config('advanced-tables.view_manager.icon', 'heroicon-o-queue-list');
    }

    public static function getViewManagerIconPosition(): null | string | IconPosition
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getViewManagerIconPosition();
        }

        return config('advanced-tables.view_manager.icon_position', IconPosition::Before);
    }

    public static function hasViewManagerBadge(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasViewManagerBadge();
        }

        return config('advanced-tables.view_manager.badge', true);
    }

    public static function canClickToApply(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->canClickToApply();
        }

        return config('advanced-tables.view_manager.click_to_apply', true);
    }

    public static function hasApplyButtonInViewManager(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasApplyButtonInViewManager();
        }

        return config('advanced-tables.view_manager.apply_button', true);
    }

    public static function hasViewTypeBadges(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasViewTypeBadges();
        }

        return config('advanced-tables.view_manager.view_type_badges', false);
    }

    public static function hasViewTypeIcons(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasViewTypeIcons();
        }

        return config('advanced-tables.view_manager.view_type_icons', true);
    }

    public static function hasPublicIndicatorWhenGlobal(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasPublicIndicatorWhenGlobal();
        }

        return config('advanced-tables.view_manager.public_indicator_when_global', false);
    }

    public static function hasActiveViewBadge(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasActiveViewBadge();
        }

        return config('advanced-tables.view_manager.active_view_badge', false);
    }

    public static function hasActiveViewIndicator(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasActiveViewIndicator();
        }

        return config('advanced-tables.view_manager.active_view_indicator', true);
    }

    public static function showViewIcon(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showViewIcon();
        }

        return config('advanced-tables.view_manager.view_icon', true);
    }

    public static function getDefaultViewIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getDefaultViewIcon();
        }

        return config('advanced-tables.view_manager.default_view_icon', 'heroicon-o-funnel');
    }
}
