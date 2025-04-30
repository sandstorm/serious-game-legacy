<?php

declare(strict_types=1);

namespace App\Providers;

use App\Authorization\AppAuthorizer;
use App\Models\User;
use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Wire Driven Ports
        // TODO add me here :)

        // Wire Driving Ports (the driven ports as dependency are automatically found)
        $this->app->scoped(ForCoreGameLogic::class, CoreGameLogicApp::class);

        //////////////////////
        /// GENERIC
        //////////////////////
        // Register Telescope only for local dev
        if ((bool) $this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(AppAuthorizer $appAuthorizer, Schedule $schedule): void
    {
        //////////////////////
        /// GENERIC
        //////////////////////
        if ((bool) $this->app->environment('production')) {
            \URL::forceScheme('https');
        } else {
            // Not production
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();

            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $schedule->command('telescope:prune')->daily();
            }
        }

        // Laravel Horizon snapshot
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        // Register our App Authorizer globally
        \Gate::before(fn (?User $user, string $ability, ...$objectAndOtherArguments) => $appAuthorizer->authorize($user, $ability, $objectAndOtherArguments));

        // we need to define the gate for accessing /pulse - the actual access check is done in AppAuthorizer.
        \Gate::define('viewPulse', function (User $user) {
            return false;
        });

        // we need to define the gate for accessing /horizon - the actual access check is done in AppAuthorizer.
        \Gate::define('viewHorizon', function (User $user) {
            return false;
        });
    }
}
