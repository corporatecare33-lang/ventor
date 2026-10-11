<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\BestSellers;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\LowStockProducts;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('LeatherX Admin')
            ->brandLogo(asset('images/brand/leatherx-logo.svg'))
            ->darkModeBrandLogo(asset('images/brand/leatherx-logo-inverse.svg'))
            ->brandLogoHeight('1.125rem')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#a8742f'),
                'gray' => Color::Stone,
            ])
            ->font('Inter', provider: LocalFontProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications(false)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Catalog'),
                NavigationGroup::make('Customers'),
                NavigationGroup::make('Services'),
                NavigationGroup::make('Marketing'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Reports'),
                NavigationGroup::make('Settings')->collapsed(),
                NavigationGroup::make('Access')->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('View store')->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt)->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->widgets([
                StatsOverview::class,
                SalesChart::class,
                LatestOrders::class,
                LowStockProducts::class,
                BestSellers::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
