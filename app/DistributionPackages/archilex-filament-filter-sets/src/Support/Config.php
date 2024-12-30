<?php

namespace Archilex\AdvancedTables\Support;

use Archilex\AdvancedTables\Support\Concerns\CanPersistViews;
use Archilex\AdvancedTables\Support\Concerns\CanReorderColumns;
use Archilex\AdvancedTables\Support\Concerns\HasFavoritesBar;
use Archilex\AdvancedTables\Support\Concerns\HasFilterBuilder;
use Archilex\AdvancedTables\Support\Concerns\HasManagedUserViews;
use Archilex\AdvancedTables\Support\Concerns\HasPresetViews;
use Archilex\AdvancedTables\Support\Concerns\HasQuickSave;
use Archilex\AdvancedTables\Support\Concerns\HasResource;
use Archilex\AdvancedTables\Support\Concerns\HasStatus;
use Archilex\AdvancedTables\Support\Concerns\HasSupport;
use Archilex\AdvancedTables\Support\Concerns\HasTenancy;
use Archilex\AdvancedTables\Support\Concerns\HasUsers;
use Archilex\AdvancedTables\Support\Concerns\HasUserViews;
use Archilex\AdvancedTables\Support\Concerns\HasViewManager;

class Config
{
    use CanPersistViews;
    use CanReorderColumns;
    use HasFavoritesBar;
    use HasFilterBuilder;
    use HasManagedUserViews;
    use HasPresetViews;
    use HasQuickSave;
    use HasResource;
    use HasStatus;
    use HasSupport;
    use HasTenancy;
    use HasUsers;
    use HasUserViews;
    use HasViewManager;

    public static function pluginRegistered(): bool
    {
        return filament()->getCurrentPanel() && filament()->hasPlugin('advanced-tables');
    }
}
