<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Enums\Status;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasStatus
{
    public static function getMinimumStatusForDisplay(): Status | string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getMinimumStatusForDisplay();
        }

        return config('advanced-tables.status.minimum_status_for_display', Status::Pending);
    }

    public static function getInitialStatus(): Status | string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getInitialStatus();
        }

        return config('advanced-tables.status.initial_status', Status::Pending);
    }
}
