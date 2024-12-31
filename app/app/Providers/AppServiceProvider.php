<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
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
        Model::preventLazyLoading(!App::isProduction());
        Model::preventSilentlyDiscardingAttributes(!App::isProduction());
        Model::preventAccessingMissingAttributes(!App::isProduction());

        if (App::isProduction()) {
            \URL::forceScheme('https');
        }
    }
}
