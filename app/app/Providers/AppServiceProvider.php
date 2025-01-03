<?php

declare(strict_types=1);

namespace App\Providers;

use App\Authorization\AppAuthorizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use Domain\NameOfCoreDomainX\CoreDomainXApp;
use Domain\NameOfCoreDomainX\DrivenPorts\ForLogging;
use Domain\NameOfCoreDomainX\DrivingPorts\ForDoingCoreBusinessLogic;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Wire Driven Ports
        $this->app->scoped(ForLogging::class, CoreDomainXApp::class);

        // Wire Driving Ports (the driven ports as dependency are automatically found)
        $this->app->scoped(ForDoingCoreBusinessLogic::class, CoreDomainXApp::class);

        // Register Telescope only for local dev
        if ((bool)$this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(AppAuthorizer $appAuthorizer): void
    {
        if ((bool)$this->app->environment('production')) {
            \URL::forceScheme('https');
        } else {
            // Not production
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();

            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                Schedule::command('telescope:prune')->daily();
            }
        }

        // Register our App Authorizer globally
        \Gate::before(fn (?User $user, string $ability, ...$objectAndOtherArguments) => $appAuthorizer->authorize($user, $ability, $objectAndOtherArguments));

        // we need to define the gate for accessing /pulse - the actual access check is done in AppAuthorizer.
        \Gate::define('viewPulse', function (User $user) {
            return false;
        });
    }
}
