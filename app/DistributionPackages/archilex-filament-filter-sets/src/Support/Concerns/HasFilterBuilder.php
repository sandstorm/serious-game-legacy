<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasFilterBuilder
{
    public static function getExpandViewStyles(): array
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getExpandViewStyles();
        }

        return config('advanced-tables.filter_builder.expand_view_styles', ['right: 80px', 'top: 24px']);
    }
}
