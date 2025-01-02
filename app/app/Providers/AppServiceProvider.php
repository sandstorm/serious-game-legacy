<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ((bool)$this->app->environment('production')) {
            \URL::forceScheme('https');
        } else {
            // Not production
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();
        }

        \Gate::define('viewPulse', function (User $user) {
            // by default, all users which are logged into Filament can access Pulse
            return $user->exists;
        });
    }
}
