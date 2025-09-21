<?php

namespace App\Providers\Filament;

use App\Filament\Mine\Widgets\ConversionRateWidget;
use App\Filament\Mine\Widgets\InventoryStatusWidget;
use App\Filament\Mine\Widgets\SalesOverviewWidget;
use App\Filament\Mine\Widgets\TopProductsTable;
use App\Filament\Mine\Widgets\TrafficSourcesChart;
use App\Http\Middleware\SetLocaleFromRequest;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
//use App\Filament\Mine\Pages\Dashboard;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Mine\Resources\Categories\CategoryResource;
use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Filament\Mine\Resources\Products\ProductResource;
use App\Filament\Mine\Resources\Users\UserResource;
use App\Filament\Mine\Resources\Vendors\VendorResource;

class MinePanelProvider extends PanelProvider
{
    /**
     * @throws \Exception
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('mine')
            ->path('mine')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                ProductResource::class,
                CategoryResource::class,
                VendorResource::class,
                OrderResource::class,
                UserResource::class,
            ])
            ->navigationGroups([
                __('shop.admin.navigation.catalog'),
                __('shop.admin.navigation.sales'),
                __('shop.admin.navigation.inventory'),
                __('shop.admin.navigation.settings'),
            ])
            ->brandName(__('shop.admin.brand'))
            ->discoverResources(in: app_path('Filament/Mine/Resources'), for: 'App\Filament\Mine\Resources')
            ->discoverPages(in: app_path('Filament/Mine/Pages'), for: 'App\Filament\Mine\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Mine/Widgets'), for: 'App\Filament\Mine\Widgets')
            ->widgets([
                AccountWidget::class,
                SalesOverviewWidget::class,
                ConversionRateWidget::class,
                TrafficSourcesChart::class,
                TopProductsTable::class,
                InventoryStatusWidget::class,
            ])
            ->renderHook('panels::user-menu.after', fn () => view('filament.mine.components.language-switcher'))
            ->middleware([
                EncryptCookies::class,
                SetLocaleFromRequest::class,
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
            ]);
    }
}
