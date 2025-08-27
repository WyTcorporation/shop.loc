<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Listeners\MergeGuestCart;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Models\Product;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
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
        Event::listen(Login::class, MergeGuestCart::class);

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(120)->by($request->ip()),
                Limit::perMinute(120)->by(optional($request->user())->id ?: 'guest'),
            ];
        });

        if (Config::get('scout.driver') === 'meilisearch') {
            Product::created(fn($p) => $p->searchable());
            Product::updated(fn($p) => $p->searchable());
            Product::deleted(fn($p) => $p->unsearchable());
        }
    }
}
