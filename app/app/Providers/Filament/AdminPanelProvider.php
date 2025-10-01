<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Models\User;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

final class AdminPanelProvider extends PanelProvider
{
    public const NAVIGATION_GROUP_STAMMDATEN = 'Stammdaten';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(self::NAVIGATION_GROUP_STAMMDATEN)
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // use the configured guard in Filament as well - by default the "web" guard is used.
            ->authGuard(config('auth.defaults.guard'))
            ->navigationItems([
                NavigationItem::make('Application Status (pulse)')
                    ->url('/pulse')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->group('Laravel Health')
                    ->sort(3),
                NavigationItem::make('Queue Monitoring (Horizon)')
                    ->url('/horizon')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->group('Laravel Health')
                    ->sort(3),
                NavigationItem::make('Health Check')
                    ->url('/up')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->group('Laravel Health')
                    ->sort(3),
            ])
            ->plugin(
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled((bool) $this->app->environment('local'))
                    ->users(fn() => User::where('email', 'LIKE', '%@example.com')->pluck('email', 'email')->toArray())
            )
            ->plugin(
                BreezyCore::make()
                    ->myProfile(
                        // for showing in main navigation: shouldRegisterNavigation: true,
                    )
                    ->enableTwoFactorAuthentication()
                // For API Access, install laravel/sanctum and enable the following line:
                // ->enableSanctumTokens()
            );
    }
}
