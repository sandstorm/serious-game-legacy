<?php

namespace Archilex\AdvancedTables\Plugin;

use Archilex\AdvancedTables\Plugin\Concerns\CanPersistViews;
use Archilex\AdvancedTables\Plugin\Concerns\CanReorderColumns;
use Archilex\AdvancedTables\Plugin\Concerns\HasFavoritesBar;
use Archilex\AdvancedTables\Plugin\Concerns\HasFilterBuilder;
use Archilex\AdvancedTables\Plugin\Concerns\HasManagedUserViews;
use Archilex\AdvancedTables\Plugin\Concerns\HasPresetViews;
use Archilex\AdvancedTables\Plugin\Concerns\HasQuickSave;
use Archilex\AdvancedTables\Plugin\Concerns\HasResource;
use Archilex\AdvancedTables\Plugin\Concerns\HasStatus;
use Archilex\AdvancedTables\Plugin\Concerns\HasSupport;
use Archilex\AdvancedTables\Plugin\Concerns\HasTenancy;
use Archilex\AdvancedTables\Plugin\Concerns\HasUsers;
use Archilex\AdvancedTables\Plugin\Concerns\HasUserViews;
use Archilex\AdvancedTables\Plugin\Concerns\HasViewManager;
use Archilex\AdvancedTables\Resources\UserViewResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;

class AdvancedTablesPlugin implements Plugin
{
    use CanPersistViews;
    use CanReorderColumns;
    use EvaluatesClosures;
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

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'advanced-tables';
    }

    public function register(Panel $panel): void
    {
        if ($this->resourceIsEnabled()) {
            $panel->resources([
                UserViewResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
