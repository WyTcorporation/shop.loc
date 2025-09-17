<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Listeners\MergeGuestCart;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;

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
        Event::listen(Login::class, MergeGuestCart::class);

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(120)->by($request->ip()),
                Limit::perMinute(120)->by(optional($request->user())->id ?: 'guest'),
            ];
        });

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

        if (Config::get('scout.driver') === 'meilisearch') {
            Product::created(fn($p) => $p->searchable());
            Product::updated(fn($p) => $p->searchable());
            Product::deleted(fn($p) => $p->unsearchable());
        }
    }
}
