<?php

namespace App\Providers;

use App\Models\Currency;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Warehouse;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\CurrencyObserver;
use App\Observers\ShipmentObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Listeners\ClaimGuestOrders;
use App\Listeners\MergeGuestCart;
use App\Listeners\SendPasswordChangedMail;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\CurrencyPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('Support/helpers.php');

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
        Currency::observe(CurrencyObserver::class);
        Shipment::observe(ShipmentObserver::class);
        Event::listen(Login::class, MergeGuestCart::class);
        Event::listen(Login::class, ClaimGuestOrders::class);
        Event::listen(PasswordReset::class, SendPasswordChangedMail::class);

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(120)->by($request->ip()),
                Limit::perMinute(120)->by(optional($request->user())->id ?: 'guest'),
            ];
        });

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(Currency::class, CurrencyPolicy::class);

        if (Config::get('scout.driver') === 'meilisearch') {
            Product::created(fn($p) => $p->searchable());
            Product::updated(fn($p) => $p->searchable());
            Product::deleted(fn($p) => $p->unsearchable());
        }
    }
}
