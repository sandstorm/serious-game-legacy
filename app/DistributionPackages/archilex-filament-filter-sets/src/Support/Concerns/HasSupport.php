<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasSupport
{
    public static function convertsIcons(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->convertsIcons();
        }

        return config('advanced-tables.support.convert_icons', false);
    }
}
