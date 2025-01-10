<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Enums\FavoritesBarTheme;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;

trait HasFavoritesBar
{
    public static function favoritesBarIsEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->favoritesBarIsEnabled();
        }

        return config('advanced-tables.favorites_bar.enabled', true);
    }

    public static function getFavoritesBarTheme(): string|FavoritesBarTheme
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getFavoritesBarTheme();
        }

        return config('advanced-tables.favorites_bar.theme', FavoritesBarTheme::Github);
    }

    public static function getFavoritesBarDefaultIcon(): ?string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getFavoritesBarDefaultIcon();
        }

        return config('advanced-tables.favorites_bar.default_icon', 'heroicon-o-bars-4');
    }

    public static function getFavoritesBarIconPosition(): null|string|IconPosition
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getFavoritesBarIconPosition();
        }

        return config('advanced-tables.favorites_bar.icon_position', IconPosition::Before);
    }

    public static function getFavoritesBarSize(): null|string|ActionSize
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getFavoritesBarSize();
        }

        return config('advanced-tables.favorites_bar.size', ActionSize::Medium);
    }

    public static function favoritesBarHasDefaultView(): null|string|ActionSize
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->favoritesBarHasDefaultView();
        }

        return config('advanced-tables.favorites_bar.default_view', true);
    }

    public static function favoritesBarHasDivider(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->favoritesBarHasDivider();
        }

        return config('advanced-tables.favorites_bar.divider', true);
    }

    public static function favoritesBarHasLoadingIndicator(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->favoritesBarHasLoadingIndicator();
        }

        return config('advanced-tables.favorites_bar.loading_indicator', false);
    }
}
