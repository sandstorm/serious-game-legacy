<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait CanPersistViews
{
    public static function persistsActiveViewInSession(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->persistsActiveViewInSession();
        }

        return config('advanced-tables.persist_active_view_in_session', false);
    }
}
