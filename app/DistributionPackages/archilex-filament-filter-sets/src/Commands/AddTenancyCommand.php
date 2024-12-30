<?php

namespace Archilex\AdvancedTables\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddTenancyCommand extends Command
{
    public $signature = 'advanced-tables:add-tenancy';

    public $description = 'Add tenancy to Advanced Tables';

    public function handle(): int
    {
        if (! $this->hasTenancy()) {
            $this->error('Please set up Tenancy in Advanced Tables before proceeding. Detailed instructions are available in the docs.');

            return static::FAILURE;
        }

        $this->info('Publishing migrations...');

        $now = Carbon::now();

        $migrationsPath = realpath(__DIR__ . '/../../database/migrations');

        $requiredMigrations = [
            'add_tenant_id_to_filter_sets_table',
            'add_tenant_id_to_managed_preset_views_table',
            'add_foreign_key_constraints_to_tenant_id',
        ];

        foreach ($requiredMigrations as $migration) {
            if (! count(glob(database_path("migrations/*_{$migration}.php")))) {
                File::copy(
                    "{$migrationsPath}/{$migration}.php.stub",
                    $this->generateMigrationName(
                        "{$migration}.php.stub",
                        $now->addSecond()
                    )
                );
            }
        }

        if (! count(glob(database_path('migrations/*_add_foreign_key_constraints_to_tenant_id.php')))) {
            File::copy(
                "{$migrationsPath}/add_foreign_key_constraints_to_tenant_id.php.stub",
                $this->generateMigrationName(
                    'add_foreign_key_constraints_to_tenant_id.php.stub',
                    $now->addSecond()
                )
            );
        }

        $this->info('Running migration...');

        $this->call('migrate');

        $this->info('Tenancy was successfully added to Advanced Tables.');

        return self::SUCCESS;
    }

    public static function generateMigrationName(string $migrationFileName, Carbon $now): string
    {
        $migrationsPath = 'migrations/';
        $migrationFileName = Str::of($migrationFileName)->rtrim('.stub')->toString();

        $len = strlen($migrationFileName) + 4;

        if (Str::contains($migrationFileName, '/')) {
            $migrationsPath .= Str::of($migrationFileName)->beforeLast('/')->finish('/');
            $migrationFileName = Str::of($migrationFileName)->afterLast('/');
        }

        foreach (glob(database_path("{$migrationsPath}*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName . '.php')) {
                return $filename;
            }
        }

        return database_path($migrationsPath . $now->format('Y_m_d_His') . '_' . Str::of($migrationFileName)->snake()->finish('.php'));
    }

    protected function hasTenancy(): bool
    {
        if (config('advanced-tables.tenancy.tenant', null)) {
            return true;
        }

        return collect(filament()->getPanels())
            ->filter(function ($panel) {
                return
                    $panel->hasTenancy() ||
                    ($panel->hasPlugin('advanced-tables') && filled($panel->getPlugin('advanced-tables')->getTenantModel()));
            })
            ->isNotEmpty();
    }
}
