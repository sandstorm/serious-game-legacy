<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Models\ManagedUserView;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasManagedUserViews
{
    public static function getManagedUserView(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getManagedUserView();
        }

        return config('advanced-tables.managed_user_view.managed_user_view', ManagedUserView::class);
    }
}
