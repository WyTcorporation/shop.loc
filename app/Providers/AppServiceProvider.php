<?php

namespace App\Providers;

use App\Models\Act;
use App\Models\CampaignTemplate;
use App\Models\CampaignTest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\CustomerSegment;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\SaftExportLog;
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
use App\Models\User;
use App\Policies\ActPolicy;
use App\Policies\CampaignTemplatePolicy;
use App\Policies\CampaignTestPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerSegmentPolicy;
use App\Policies\CurrencyPolicy;
use App\Policies\DeliveryNotePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MarketingCampaignPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\SaftExportLogPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;

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
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(Currency::class, CurrencyPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(DeliveryNote::class, DeliveryNotePolicy::class);
        Gate::policy(Act::class, ActPolicy::class);
        Gate::policy(SaftExportLog::class, SaftExportLogPolicy::class);
        Gate::policy(MarketingCampaign::class, MarketingCampaignPolicy::class);
        Gate::policy(CustomerSegment::class, CustomerSegmentPolicy::class);
        Gate::policy(CampaignTemplate::class, CampaignTemplatePolicy::class);
        Gate::policy(CampaignTest::class, CampaignTestPolicy::class);
        Gate::policy(SpatieRole::class, RolePolicy::class);
        Gate::policy(SpatiePermission::class, PermissionPolicy::class);

        if (
            Config::get('filesystems.disks.public.driver') === 'local'
            && (! app()->runningInConsole() || app()->runningUnitTests())
            && ! file_exists(public_path('storage'))
        ) {
            try {
                app('files')->link(storage_path('app/public'), public_path('storage'));
            } catch (\Throwable) {
                // Immutable filesystems may not allow creating the link. That's fine.
            }
        }

        if (Config::get('scout.driver') === 'meilisearch') {
            Product::created(fn($p) => $p->searchable());
            Product::updated(fn($p) => $p->searchable());
            Product::deleted(fn($p) => $p->unsearchable());
        }
    }
}
