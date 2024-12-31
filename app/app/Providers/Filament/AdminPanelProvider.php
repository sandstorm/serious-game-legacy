<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Models\User;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
use RalphJSmit\Filament\RecordFinder\FilamentRecordFinder;

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
            ->viteTheme('resources/css/filament/admin/theme.css')
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
            ->plugin(
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users(fn () => User::where('email', 'LIKE', '%@example.com')->pluck('email', 'email')->toArray())
            )
            ->plugin(
                AdvancedTablesPlugin::make()
                    ->quickSaveMakeGlobalFavorite() // every user can make a global favorite
                    ->resourceNavigationGroup(self::NAVIGATION_GROUP_STAMMDATEN)
                    ->resourceNavigationIcon(null)
            )
            ->when( // while running "dev composer-update-filament-record-finder-pro", the existing plugin needs to be removed temporarily - this is to ensure we won't crash here
                class_exists(FilamentRecordFinder::class),
                fn ($panel) => $panel->plugin(
                    FilamentRecordFinder::make()
                )
            );
    }
}
