<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
