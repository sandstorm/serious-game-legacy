<?php

namespace RalphJSmit\Filament\RecordFinder;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentRecordFinder implements Plugin
{
    public const RENDER_HOOK_RECORD_FINDER_TABLE_BEFORE = 'record-finder::livewire.record-finder-table.before';

    public const RENDER_HOOK_RECORD_FINDER_TABLE_AFTER = 'record-finder::livewire.record-finder-table.after';

    public static function make(): static
    {
        $plugin = app(static::class);

        $plugin->setUp();

        return $plugin;
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public static function isRegistered(): bool
    {
        return filament()->hasPlugin(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'ralphjsmit/laravel-filament-record-finder';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function setUp(): void
    {
        //
    }
}
