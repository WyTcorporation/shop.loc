<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\ShipmentStatus;
use App\Models\Address;
use App\Models\Act;
use App\Models\CampaignTemplate;
use App\Models\CampaignTest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Currency;
use App\Models\CustomerSegment;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\LoyaltyPointTransaction;
use App\Models\MarketingCampaign;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SaftExportLog;
use App\Models\Product;
use App\Models\Review;
use App\Models\TwoFactorSecret;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Wishlist;
use Database\Seeders\Concerns\GeneratesLocalizedText;
use Database\Support\TranslationGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class FullDemoSeeder extends Seeder
{
    use GeneratesLocalizedText;
    public function run(): void
    {
        $this->resetMedia();

        $this->call(DemoCatalogSeeder::class);

        $this->seedPermissionsAndRoles();

        $users = $this->seedUsers();
        $coupons = $this->seedCoupons();
        $currencies = $this->seedCurrencies();
        $warehouses = $this->seedWarehouses();

        $this->seedWarehouseStock($warehouses);
        $this->seedCarts($users, $coupons);
        $orders = $this->seedOrders($users, $coupons, $currencies, $warehouses);
        $this->seedInvoices($orders);
        $this->seedDeliveryNotes($orders);
        $this->seedActs($orders);
        $this->seedSaftExportLogs($orders, $users);
        $templates = $this->seedCampaignTemplates();
        $segments = $this->seedCustomerSegments();
        $campaigns = $this->seedMarketingCampaigns($templates, $segments);
        $this->seedCampaignTests($campaigns, $templates);
        $this->seedLoyaltyTransactions($users, $orders);
        $this->seedMessages($orders, $users);
        $this->seedReviewsAndWishlists($users);
    }

    private function resetMedia(): void
    {
        $disk = Storage::disk('public');
        $disk->deleteDirectory('products');
        $disk->makeDirectory('products');
    }

    private function seedPermissionsAndRoles(): void
    {
        $permissions = collect(PermissionEnum::values())->map(function (string $permission) {
            return Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        });

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = SpatieRole::query()->firstOrCreate([
                'name' => $roleEnum->value,
                'guard_name' => 'web',
            ]);

            $permissionNames = match ($roleEnum) {
                RoleEnum::Administrator => $permissions->pluck('name')->all(),
                RoleEnum::Accountant => [
                    PermissionEnum::ViewInvoices->value,
                    PermissionEnum::ManageInvoices->value,
                    PermissionEnum::ViewDeliveryNotes->value,
                    PermissionEnum::ManageDeliveryNotes->value,
                    PermissionEnum::ViewActs->value,
                    PermissionEnum::ManageActs->value,
                    PermissionEnum::ViewSaftExports->value,
                    PermissionEnum::ManageSaftExports->value,
                ],
                RoleEnum::CatalogManager => [
                    PermissionEnum::ViewProducts->value,
                    PermissionEnum::ManageProducts->value,
                    PermissionEnum::ManageInventory->value,
                ],
                RoleEnum::OrderManager => [
                    PermissionEnum::ViewOrders->value,
                    PermissionEnum::ManageOrders->value,
                    PermissionEnum::ViewDeliveryNotes->value,
                    PermissionEnum::ManageDeliveryNotes->value,
                    PermissionEnum::ViewInvoices->value,
                    PermissionEnum::ManageInvoices->value,
                    PermissionEnum::ViewActs->value,
                    PermissionEnum::ManageActs->value,
                    PermissionEnum::ViewSaftExports->value,
                    PermissionEnum::ManageSaftExports->value,
                ],
                RoleEnum::MarketingManager => [
                    PermissionEnum::ViewMarketing->value,
                    PermissionEnum::ManageMarketing->value,
                    PermissionEnum::ViewCampaigns->value,
                    PermissionEnum::ManageCampaigns->value,
                    PermissionEnum::ViewSegments->value,
                    PermissionEnum::ManageSegments->value,
                    PermissionEnum::ViewCampaignTemplates->value,
                    PermissionEnum::ManageCampaignTemplates->value,
                    PermissionEnum::ViewCampaignTests->value,
                    PermissionEnum::ManageCampaignTests->value,
                ],
                RoleEnum::Support => [
                    PermissionEnum::ViewOrders->value,
                    PermissionEnum::ManageOrders->value,
                    PermissionEnum::ViewUsers->value,
                ],
            };

            $role->syncPermissions($permissionNames);
        }
    }

    private function seedUsers(): Collection
    {
        $categorySlugs = Category::query()
            ->orderBy('id')
            ->pluck('slug')
            ->values();

        $usersConfig = [
            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => 'admin',
                'two_factor' => false,
                'addresses' => 1,
                'roles' => [RoleEnum::Administrator->value],
                'permissions' => [],
                'categories' => [],
            ],
            'buyer' => [
                'name' => 'Demo Buyer',
                'email' => 'demo+buyer@example.com',
                'password' => 'password',
                'two_factor' => true,
                'addresses' => 2,
                'roles' => [],
                'permissions' => [],
                'categories' => [],
            ],
            'repeat' => [
                'name' => 'Repeat Customer',
                'email' => 'demo+repeat@example.com',
                'password' => 'password',
                'two_factor' => true,
                'addresses' => 2,
                'roles' => [],
                'permissions' => [],
                'categories' => [],
            ],
            'vip' => [
                'name' => 'VIP Shopper',
                'email' => 'demo+vip@example.com',
                'password' => 'password',
                'two_factor' => true,
                'addresses' => 3,
                'roles' => [],
                'permissions' => [],
                'categories' => [],
            ],
            'accountant' => [
                'name' => 'Demo Accountant',
                'email' => 'demo+accountant@example.com',
                'password' => 'password',
                'two_factor' => true,
                'addresses' => 1,
                'roles' => [RoleEnum::Accountant->value],
                'permissions' => [],
                'categories' => [],
            ],
            'marketing_manager' => [
                'name' => 'Demo Marketing Manager',
                'email' => 'demo+marketing@example.com',
                'password' => 'password',
                'two_factor' => false,
                'addresses' => 1,
                'roles' => [RoleEnum::MarketingManager->value],
                'permissions' => [],
                'categories' => [],
            ],
            'catalog_manager_one' => [
                'name' => 'Demo Catalog Manager A',
                'email' => 'demo+catalog-a@example.com',
                'password' => 'password',
                'two_factor' => false,
                'addresses' => 1,
                'roles' => [],
                'permissions' => [
                    PermissionEnum::ViewProducts->value,
                    PermissionEnum::ManageProducts->value,
                    PermissionEnum::ManageInventory->value,
                ],
                'categories' => $categorySlugs->take(2)->all(),
            ],
            'catalog_manager_two' => [
                'name' => 'Demo Catalog Manager B',
                'email' => 'demo+catalog-b@example.com',
                'password' => 'password',
                'two_factor' => false,
                'addresses' => 1,
                'roles' => [],
                'permissions' => [
                    PermissionEnum::ViewProducts->value,
                    PermissionEnum::ManageProducts->value,
                ],
                'categories' => $categorySlugs->slice(2, 3)->all(),
            ],
        ];

        return collect($usersConfig)->map(function (array $config) {
            $user = User::updateOrCreate(
                ['email' => $config['email']],
                [
                    'name' => $config['name'],
                    'password' => Hash::make($config['password']),
                ]
            );

            if ($config['two_factor']) {
                $user->twoFactorSecret()?->delete();
                TwoFactorSecret::factory()->for($user)->create();
            }

            $user->addresses()->delete();
            if ($config['addresses'] > 0) {
                Address::factory()->count($config['addresses'])->for($user)->create();
            }

            $user->syncRoles($config['roles']);
            $user->syncPermissions($config['permissions']);

            $categoryIds = Category::query()
                ->whereIn('slug', $config['categories'])
                ->pluck('id')
                ->all();
            $user->categories()->sync($categoryIds);

            return $user->fresh(['addresses', 'twoFactorSecret']);
        });
    }

    private function seedCoupons(): Collection
    {
        $couponData = [
            [
                'code' => 'WELCOME10',
                'translation_key' => 'welcome',
                'type' => Coupon::TYPE_PERCENT,
                'value' => 10,
                'max_discount' => 50,
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP15',
                'translation_key' => 'shipping',
                'type' => Coupon::TYPE_FIXED,
                'value' => 15,
                'min_cart_total' => 60,
                'max_discount' => null,
                'usage_limit' => 200,
                'per_user_limit' => 3,
                'starts_at' => now()->subWeeks(2),
                'expires_at' => now()->addWeeks(6),
            ],
            [
                'code' => 'VIP20',
                'translation_key' => 'vip',
                'type' => Coupon::TYPE_PERCENT,
                'value' => 20,
                'max_discount' => 120,
                'usage_limit' => null,
                'per_user_limit' => null,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(2),
            ],
        ];

        return collect($couponData)->map(function (array $data) {
            $texts = TranslationGenerator::couponTexts($data['translation_key']);
            $name = $this->localized($texts['name']);
            $description = $this->localized($texts['description']);

            $attributes = array_merge([
                'min_cart_total' => $data['min_cart_total'] ?? 0,
                'is_active' => true,
                'used' => 0,
                'meta' => null,
            ], $data, [
                'name' => $name['value'],
                'name_translations' => $name['translations'],
                'description' => $description['value'],
                'description_translations' => $description['translations'],
            ]);

            unset($attributes['translation_key']);

            return Coupon::updateOrCreate(
                ['code' => $data['code']],
                $attributes
            );
        })->keyBy('code');
    }

    private function seedCurrencies(): Collection
    {
        $base = strtoupper(config('shop.currency.base', 'EUR'));
        $rates = [
            $base => 1.0,
            'USD' => 1.09,
            'GBP' => 0.86,
            'PLN' => 4.31,
        ];

        return collect($rates)->map(function (float $rate, string $code) {
            return Currency::updateOrCreate(
                ['code' => $code],
                ['rate' => $rate]
            );
        })->keyBy('code');
    }

    private function seedWarehouses(): Collection
    {
        $mainLabels = TranslationGenerator::warehouseTexts('main');
        $mainName = $this->localized($mainLabels['name']);
        $mainDescription = $this->localized($mainLabels['description']);

        $main = Warehouse::updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => $mainName['value'],
                'name_translations' => $mainName['translations'],
                'description' => $mainDescription['value'],
                'description_translations' => $mainDescription['translations'],
            ]
        );

        $warehouseConfigs = [
            ['code' => 'EU-HUB', 'key' => 'eu'],
            ['code' => 'US-COAST', 'key' => 'us'],
        ];

        $additional = collect($warehouseConfigs)->map(function (array $config) {
            $labels = TranslationGenerator::warehouseTexts($config['key']);
            $name = $this->localized($labels['name']);
            $description = $this->localized($labels['description']);

            $attributes = Warehouse::factory()->state([
                'code' => $config['code'],
                'name' => $name['value'],
                'name_translations' => $name['translations'],
                'description' => $description['value'],
                'description_translations' => $description['translations'],
            ])->make()->toArray();

            return Warehouse::updateOrCreate(
                ['code' => $config['code']],
                $attributes
            );
        });

        return collect([$main])->merge($additional)->keyBy('code');
    }

    private function seedWarehouseStock(Collection $warehouses): void
    {
        $products = Product::orderBy('id')->take(12)->get();

        if ($products->isEmpty()) {
            return;
        }

        $products->each(function (Product $product, int $index) use ($warehouses) {
            $warehouses->values()->each(function (Warehouse $warehouse, int $offset) use ($product, $index) {
                $qty = 20 + ($index + 1) * (2 + $offset);
                $reserved = (int) floor($qty * (0.1 + $offset * 0.05));
                $reserved = min($reserved, $qty - 1);

                $product->stocks()->updateOrCreate(
                    ['warehouse_id' => $warehouse->id],
                    [
                        'qty' => $qty,
                        'reserved' => max(0, $reserved),
                    ]
                );
            });

            $product->syncAvailableStock();
        });
    }

    private function seedCarts(Collection $users, Collection $coupons): void
    {
        $userIds = $users->except('admin')->pluck('id');
        Cart::whereIn('user_id', $userIds)->delete();

        $products = Product::orderBy('id')->take(12)->get();
        if ($products->isEmpty()) {
            return;
        }

        $cartConfigs = [
            [
                'user_key' => 'buyer',
                'coupon' => $coupons->get('WELCOME10'),
                'points' => 120,
                'status' => 'active',
            ],
            [
                'user_key' => 'repeat',
                'coupon' => $coupons->get('FREESHIP15'),
                'points' => 200,
                'status' => 'active',
            ],
            [
                'user_key' => 'vip',
                'coupon' => $coupons->get('VIP20'),
                'points' => 0,
                'status' => 'ordered',
            ],
        ];

        foreach ($cartConfigs as $position => $config) {
            $user = $users->get($config['user_key']);
            if (! $user) {
                continue;
            }

            $coupon = $config['coupon'];

            $cart = Cart::factory()->create([
                'user_id' => $user->id,
                'status' => $config['status'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'loyalty_points_used' => $config['points'],
            ]);

            CartItem::factory()->count(3)->sequence(
                fn ($sequence) => [
                    'cart_id' => $cart->id,
                    'product_id' => $products[($position * 3 + $sequence->index) % $products->count()]->id,
                    'price' => (float) $products[($position * 3 + $sequence->index) % $products->count()]->price,
                    'qty' => $sequence->index + 1,
                ]
            )->create();
        }
    }

    private function seedOrders(Collection $users, Collection $coupons, Collection $currencies, Collection $warehouses): Collection
    {
        $ordersConfig = [
            'new' => [
                'number' => 'ORD-DEMO-NEW',
                'status' => OrderStatus::New,
                'user_key' => 'buyer',
                'currency' => $currencies->get(config('shop.currency.base', 'EUR'))?->code ?? 'EUR',
                'coupon' => $coupons->get('WELCOME10'),
                'points_used' => 0,
                'shipment_status' => ShipmentStatus::Pending,
                'payment_status' => 'pending',
            ],
            'paid' => [
                'number' => 'ORD-DEMO-PAID',
                'status' => OrderStatus::Paid,
                'user_key' => 'repeat',
                'currency' => $currencies->get('USD')?->code ?? 'USD',
                'coupon' => $coupons->get('FREESHIP15'),
                'points_used' => 200,
                'shipment_status' => ShipmentStatus::Processing,
                'payment_status' => 'succeeded',
                'paid_at' => now()->subDays(1),
            ],
            'shipped' => [
                'number' => 'ORD-DEMO-SHIPPED',
                'status' => OrderStatus::Shipped,
                'user_key' => 'vip',
                'currency' => $currencies->get('GBP')?->code ?? 'GBP',
                'coupon' => $coupons->get('VIP20'),
                'points_used' => 150,
                'shipment_status' => ShipmentStatus::Shipped,
                'payment_status' => 'succeeded',
                'paid_at' => now()->subDays(3),
                'shipped_at' => now()->subDay(),
                'inventory_committed_at' => now()->subDays(2),
            ],
            'cancelled' => [
                'number' => 'ORD-DEMO-CANCELLED',
                'status' => OrderStatus::Cancelled,
                'user_key' => 'buyer',
                'currency' => $currencies->get('PLN')?->code ?? 'PLN',
                'coupon' => null,
                'points_used' => 0,
                'shipment_status' => ShipmentStatus::Cancelled,
                'payment_status' => 'canceled',
                'cancelled_at' => now()->subHours(6),
            ],
        ];

        Order::whereIn('number', collect($ordersConfig)->pluck('number'))->delete();

        $products = Product::orderBy('id')->take(15)->get();
        $orders = collect();

        foreach ($ordersConfig as $key => $config) {
            $user = $users->get($config['user_key']);
            if (! $user) {
                continue;
            }

            $address = $user->addresses()->first();
            $shipping = $address ? [
                'name' => $address->name,
                'city' => $address->city,
                'addr' => $address->addr,
                'postal_code' => $address->postal_code,
                'phone' => $address->phone,
            ] : null;

            $order = Order::factory()
                ->for($user)
                ->state([
                    'number' => $config['number'],
                    'status' => $config['status'],
                    'currency' => $config['currency'],
                    'coupon_id' => $config['coupon']?->id,
                    'coupon_code' => $config['coupon']?->code,
                    'loyalty_points_used' => $config['points_used'],
                    'loyalty_points_value' => 0,
                    'loyalty_points_earned' => 0,
                    'payment_status' => $config['payment_status'],
                    'paid_at' => $config['paid_at'] ?? null,
                    'shipped_at' => $config['shipped_at'] ?? null,
                    'cancelled_at' => $config['cancelled_at'] ?? null,
                    'inventory_committed_at' => $config['inventory_committed_at'] ?? null,
                    'shipping_address_id' => $address?->id,
                    'shipping_address' => $shipping,
                    'billing_address' => $shipping,
                    'email' => $user->email,
                ])->create();

            $items = OrderItem::factory()->count(3)->sequence(
                fn ($sequence) => [
                    'order_id' => $order->id,
                    'product_id' => $products[($sequence->index) % $products->count()]->id,
                    'warehouse_id' => $warehouses->values()[($sequence->index) % $warehouses->count()]->id,
                    'price' => (float) $products[($sequence->index) % $products->count()]->price,
                    'qty' => $sequence->index + 1,
                ]
            )->create();

            $subtotal = $items->sum(fn (OrderItem $item) => $item->qty * (float) $item->price);
            $couponDiscount = $config['coupon'] ? $config['coupon']->calculateDiscount($subtotal) : 0;
            $loyaltyValue = $config['points_used'] * (float) config('shop.loyalty.redeem_value', 0.1);
            $discountTotal = $couponDiscount + $loyaltyValue;

            $order->forceFill([
                'subtotal' => $subtotal,
                'coupon_discount' => $couponDiscount,
                'discount_total' => $discountTotal,
                'loyalty_points_value' => $loyaltyValue,
                'loyalty_points_earned' => max(0, (int) round($subtotal / 10)),
                'total' => max(0, $subtotal - $discountTotal),
            ])->save();

            $shipment = $order->shipment;
            if ($shipment) {
                $shipment->update([
                    'status' => $config['shipment_status'],
                    'tracking_number' => $config['shipment_status'] === ShipmentStatus::Cancelled
                        ? null
                        : 'TRK-'.$order->id,
                    'shipped_at' => $config['shipped_at'] ?? null,
                    'delivered_at' => $config['status'] === OrderStatus::Shipped ? now() : null,
                ]);
            }

            $orders->put($key, $order->fresh(['items', 'shipment']));
        }

        return $orders;
    }

    private function seedInvoices(Collection $orders): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        $configs = [
            'new' => [
                'status' => 'draft',
                'issued_days_ago' => 1,
                'due_in_days' => 14,
                'tax_rate' => 0.18,
                'notes' => 'Issued for review before payment capture.',
            ],
            'paid' => [
                'status' => 'paid',
                'issued_days_ago' => 8,
                'due_in_days' => 0,
                'tax_rate' => 0.21,
                'notes' => 'Payment received in full via credit card.',
            ],
            'shipped' => [
                'status' => 'issued',
                'issued_days_ago' => 5,
                'due_in_days' => 7,
                'tax_rate' => 0.2,
                'notes' => 'Partial payment expected upon delivery.',
            ],
            'cancelled' => [
                'status' => 'void',
                'issued_days_ago' => 2,
                'due_in_days' => null,
                'tax_rate' => 0,
                'notes' => 'Cancelled before fulfillment. No payment due.',
            ],
        ];

        Invoice::query()->whereIn('order_id', $orders->pluck('id'))->delete();

        foreach ($orders as $key => $order) {
            $order->loadMissing(['items.product', 'user']);

            $config = $configs[$key] ?? [
                'status' => 'draft',
                'issued_days_ago' => 0,
                'due_in_days' => 14,
                'tax_rate' => 0.2,
                'notes' => 'Auto-generated invoice for demo data.',
            ];

            $issuedAt = now()->subDays($config['issued_days_ago']);
            $dueAt = is_null($config['due_in_days']) ? null : $issuedAt->copy()->addDays($config['due_in_days']);

            $subtotal = (float) $order->subtotal;
            $taxTotal = round($subtotal * $config['tax_rate'], 2);
            $discountTotal = (float) $order->discount_total;
            $total = max(0, round($subtotal + $taxTotal - $discountTotal, 2));

            $lineSummary = $order->items
                ->map(fn ($item) => sprintf(
                    '%s × %d @ %s',
                    Str::limit($item->product?->name ?? 'Item '.$item->id, 40),
                    (int) $item->qty,
                    number_format((float) $item->price, 2)
                ))
                ->implode('; ');

            Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV-'.Str::of($order->number)->after('ORD-')->replace('-', ''),
                'issued_at' => $issuedAt,
                'due_at' => $dueAt,
                'status' => $config['status'],
                'currency' => $order->currency,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'metadata' => [
                    'payment_terms' => $dueAt ? sprintf('Net %d days', $config['due_in_days']) : 'No payment due',
                    'customer_reference' => $order->user?->name ?? $order->email,
                    'line_items' => $lineSummary,
                    'notes' => $config['notes'],
                ],
            ]);
        }
    }

    private function seedDeliveryNotes(Collection $orders): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        $configs = [
            'new' => [
                'status' => 'draft',
                'issued_days_ago' => 0,
                'dispatch_delay' => null,
                'remarks' => 'Awaiting warehouse confirmation.',
            ],
            'paid' => [
                'status' => 'packing',
                'issued_days_ago' => 4,
                'dispatch_delay' => 1,
                'remarks' => 'Preparing shipment with gift packaging.',
            ],
            'shipped' => [
                'status' => 'dispatched',
                'issued_days_ago' => 3,
                'dispatch_delay' => 0,
                'remarks' => 'Left the warehouse with priority courier.',
            ],
            'cancelled' => [
                'status' => 'void',
                'issued_days_ago' => 1,
                'dispatch_delay' => null,
                'remarks' => 'Cancelled prior to dispatch.',
            ],
        ];

        DeliveryNote::query()->whereIn('order_id', $orders->pluck('id'))->delete();

        foreach ($orders as $key => $order) {
            $order->loadMissing(['items.product', 'items.warehouse']);

            $config = $configs[$key] ?? [
                'status' => 'draft',
                'issued_days_ago' => 0,
                'dispatch_delay' => null,
                'remarks' => 'Auto-generated delivery note.',
            ];

            $issuedAt = now()->subDays($config['issued_days_ago']);
            $dispatchedAt = is_null($config['dispatch_delay']) ? null : $issuedAt->copy()->addDays($config['dispatch_delay']);

            $items = $order->items
                ->map(fn ($item) => [
                    'sku' => $item->product?->sku ?? 'SKU-'.$item->id,
                    'name' => $item->product?->name ?? 'Item '.$item->id,
                    'quantity' => (int) $item->qty,
                    'warehouse' => $item->warehouse?->name,
                ])
                ->values()
                ->all();

            DeliveryNote::create([
                'order_id' => $order->id,
                'number' => 'DN-'.Str::of($order->number)->after('ORD-')->replace('-', ''),
                'issued_at' => $issuedAt,
                'dispatched_at' => $dispatchedAt,
                'status' => $config['status'],
                'items' => $items,
                'remarks' => $config['remarks'],
            ]);
        }
    }

    private function seedActs(Collection $orders): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        $configs = [
            'new' => [
                'status' => 'draft',
                'issued_days_ago' => 0,
                'description' => 'Pending confirmation of delivered services.',
            ],
            'paid' => [
                'status' => 'approved',
                'issued_days_ago' => 7,
                'description' => 'Services rendered and approved by finance.',
            ],
            'shipped' => [
                'status' => 'signed',
                'issued_days_ago' => 4,
                'description' => 'Signed upon delivery completion.',
            ],
            'cancelled' => [
                'status' => 'void',
                'issued_days_ago' => 2,
                'description' => 'Cancelled order – record kept for auditing.',
            ],
        ];

        Act::query()->whereIn('order_id', $orders->pluck('id'))->delete();

        foreach ($orders as $key => $order) {
            $config = $configs[$key] ?? [
                'status' => 'draft',
                'issued_days_ago' => 0,
                'description' => 'Auto-generated act for demo data.',
            ];

            $issuedAt = now()->subDays($config['issued_days_ago']);

            Act::create([
                'order_id' => $order->id,
                'number' => 'ACT-'.Str::of($order->number)->after('ORD-')->replace('-', ''),
                'issued_at' => $issuedAt,
                'status' => $config['status'],
                'total' => (float) $order->total,
                'description' => $config['description'],
            ]);
        }
    }

    private function seedSaftExportLogs(Collection $orders, Collection $users): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        $accountant = $users->get('accountant');

        if (! $accountant) {
            return;
        }

        $configs = [
            [
                'order_key' => 'paid',
                'format' => 'xml',
                'status' => 'completed',
                'file_path' => 'exports/saft/demo-orders-q1.xml',
                'filters' => ['status' => 'paid', 'from_date' => now()->startOfYear()->toDateString(), 'to_date' => now()->toDateString()],
                'exported_days_ago' => 3,
                'message' => 'Export finished successfully for paid orders.',
            ],
            [
                'order_key' => 'shipped',
                'format' => 'json',
                'status' => 'processing',
                'file_path' => null,
                'filters' => ['status' => 'shipped', 'warehouse' => 'Main'],
                'exported_days_ago' => null,
                'message' => 'Export queued and processing in the background.',
            ],
            [
                'order_key' => 'cancelled',
                'format' => 'xml',
                'status' => 'failed',
                'file_path' => null,
                'filters' => ['status' => 'cancelled', 'include_documents' => true],
                'exported_days_ago' => null,
                'message' => 'Failed due to missing tax profile configuration.',
            ],
        ];

        SaftExportLog::query()
            ->whereIn('status', ['completed', 'processing', 'failed'])
            ->where('message', 'like', 'Export%')
            ->delete();

        Storage::disk('local')->makeDirectory('exports/saft');

        foreach ($configs as $config) {
            $order = $orders->get($config['order_key']);

            if (! $order) {
                continue;
            }

            $exportedAt = $config['exported_days_ago'] === null
                ? null
                : now()->subDays($config['exported_days_ago']);

            if ($config['file_path']) {
                Storage::disk('local')->put($config['file_path'], 'Demo SAF-T export content for showcase.');
            }

            SaftExportLog::create([
                'order_id' => $order->id,
                'user_id' => $accountant->id,
                'format' => $config['format'],
                'status' => $config['status'],
                'file_path' => $config['file_path'],
                'filters' => $config['filters'],
                'exported_at' => $exportedAt,
                'message' => $config['message'],
            ]);
        }
    }

    private function seedCampaignTemplates(): Collection
    {
        $templateConfigs = [
            MarketingCampaign::TYPE_EMAIL => [
                'welcome_series' => [
                    'name' => 'Welcome Series Email',
                    'subject' => 'Welcome to ShopWave! Start your journey',
                    'content' => implode(PHP_EOL, [
                        '<h1>Welcome to ShopWave</h1>',
                        '<p>Thanks for joining our community! Discover curated picks chosen for you and enjoy member-only perks.</p>',
                        '<p><a href="https://shop.example.com/new-arrivals">Browse the latest arrivals</a> and claim your 10% welcome perk.</p>',
                    ]),
                    'meta' => [
                        'preview_text' => 'Meet your new favorite products and enjoy a welcome perk.',
                        'cta' => 'Shop new arrivals',
                    ],
                ],
                'weekly_digest' => [
                    'name' => 'Weekly Highlights Digest',
                    'subject' => 'This week’s best sellers & stories',
                    'content' => implode(PHP_EOL, [
                        '<h2>Your weekly ShopWave round-up</h2>',
                        '<ul>',
                        '    <li>Top-selling essentials picked by our community.</li>',
                        '    <li>Fresh editorial guides to inspire your next purchase.</li>',
                        '    <li>Member-only deals expiring this weekend.</li>',
                        '</ul>',
                        '<p>See everything that’s trending before it’s gone.</p>',
                    ]),
                    'meta' => [
                        'preview_text' => 'Handpicked deals, editorials, and community favorites—just for you.',
                        'cta' => 'Explore the digest',
                    ],
                ],
                'loyalty_spotlight' => [
                    'name' => 'Loyalty Spotlight Email',
                    'subject' => 'You’re only one order away from Platinum status',
                    'content' => implode(PHP_EOL, [
                        '<h2>Unlock Platinum perks</h2>',
                        '<p>Your loyalty balance is climbing fast. Check out exclusive bundles that earn double points this week.</p>',
                        '<p><a href="https://shop.example.com/loyalty">View your rewards dashboard</a> and turn points into savings.</p>',
                    ]),
                    'meta' => [
                        'preview_text' => 'Earn double points on curated bundles—limited time only.',
                        'cta' => 'See rewards',
                    ],
                ],
            ],
            MarketingCampaign::TYPE_PUSH => [
                'flash_sale' => [
                    'name' => 'Flash Sale Push Notification',
                    'subject' => '⏰ 4-hour flash sale!',
                    'content' => 'Tap to unlock 25% off sitewide before the timer ends.',
                    'meta' => [
                        'cta' => 'Shop the flash sale',
                        'deep_link' => 'shopwave://flash-sale',
                    ],
                ],
                'winback_nudge' => [
                    'name' => 'Win-back Reminder Push',
                    'subject' => 'We saved something you’ll love',
                    'content' => 'Come back for curated picks waiting in your account—plus a surprise treat.',
                    'meta' => [
                        'cta' => 'See your picks',
                        'deep_link' => 'shopwave://home',
                    ],
                ],
                'back_in_stock' => [
                    'name' => 'Back in Stock Push Alert',
                    'subject' => 'It’s back! Grab it before it sells out again',
                    'content' => 'A wishlist favorite just returned. Reserve yours now before the next wave sells out.',
                    'meta' => [
                        'cta' => 'Reserve now',
                        'deep_link' => 'shopwave://wishlist',
                    ],
                ],
            ],
        ];

        return collect($templateConfigs)->map(function (array $templates, string $channel) {
            return collect($templates)->map(function (array $config) use ($channel) {
                return CampaignTemplate::updateOrCreate(
                    [
                        'name' => $config['name'],
                        'channel' => $channel,
                    ],
                    [
                        'subject' => $config['subject'],
                        'content' => $config['content'],
                        'meta' => $config['meta'],
                    ]
                );
            });
        });
    }

    private function seedCustomerSegments(): Collection
    {
        $segmentConfigs = [
            'new_customers' => [
                'name' => 'New customers (last 30 days)',
                'description' => 'Recently registered shoppers with verified email addresses.',
                'conditions' => [
                    'created_after' => now()->subDays(30)->toDateString(),
                    'email_verified' => true,
                ],
                'is_active' => true,
            ],
            'vip_verified' => [
                'name' => 'VIP verified customers',
                'description' => 'Long-term customers from our VIP domain with verified accounts.',
                'conditions' => [
                    'created_before' => now()->subMonths(6)->toDateString(),
                    'email_verified' => true,
                    'email_domain' => 'vip.example.com',
                ],
                'is_active' => true,
            ],
            'lapsed_accounts' => [
                'name' => 'Lapsed accounts',
                'description' => 'Contacts who joined more than a year ago and need a win-back.',
                'conditions' => [
                    'created_before' => now()->subYear()->toDateString(),
                    'email_verified' => false,
                ],
                'is_active' => false,
            ],
        ];

        return collect($segmentConfigs)->map(function (array $config) {
            return CustomerSegment::updateOrCreate(
                ['name' => $config['name']],
                [
                    'description' => $config['description'],
                    'conditions' => $config['conditions'],
                    'is_active' => $config['is_active'],
                ]
            );
        });
    }

    private function seedMarketingCampaigns(Collection $templates, Collection $segments): Collection
    {
        $emailTemplates = $templates->get(MarketingCampaign::TYPE_EMAIL, collect());
        $pushTemplates = $templates->get(MarketingCampaign::TYPE_PUSH, collect());

        $campaignConfigs = [
            'welcome_drip' => [
                'name' => 'Welcome Drip Series',
                'type' => MarketingCampaign::TYPE_EMAIL,
                'template' => $emailTemplates->get('welcome_series'),
                'status' => 'draft',
                'settings' => [
                    'utm_campaign' => 'welcome-drip',
                    'sender' => 'community@shopwave.test',
                ],
                'audience_filters' => [
                    'include_segments' => ['new_customers'],
                ],
                'scheduled_for' => now()->addDays(2),
                'last_dispatched_at' => null,
                'last_synced_at' => null,
                'metrics' => [
                    'open_count' => 0,
                    'click_count' => 0,
                    'conversion_count' => 0,
                ],
                'segments' => ['new_customers'],
                'schedule' => [
                    'cron_expression' => '0 9 * * MON',
                    'timezone' => config('app.timezone', 'UTC'),
                    'starts_at' => now()->addDays(2),
                    'ends_at' => now()->addWeeks(4),
                    'next_run_at' => now()->addDays(2),
                    'is_active' => false,
                ],
            ],
            'vip_flash_sale_email' => [
                'name' => 'VIP Flash Sale Email',
                'type' => MarketingCampaign::TYPE_EMAIL,
                'template' => $emailTemplates->get('loyalty_spotlight'),
                'status' => 'scheduled',
                'settings' => [
                    'utm_campaign' => 'vip-flash-sale',
                    'offer_code' => 'FLASH25',
                ],
                'audience_filters' => [
                    'minimum_orders' => 3,
                ],
                'scheduled_for' => now()->addHours(6),
                'last_dispatched_at' => null,
                'last_synced_at' => now()->subHours(1),
                'metrics' => [
                    'open_count' => 240,
                    'click_count' => 96,
                    'conversion_count' => 28,
                ],
                'segments' => ['vip_verified'],
                'schedule' => [
                    'cron_expression' => '30 7 * * FRI',
                    'timezone' => config('app.timezone', 'UTC'),
                    'starts_at' => now()->addHours(6),
                    'ends_at' => now()->addWeeks(2),
                    'next_run_at' => now()->addHours(6),
                    'is_active' => true,
                ],
            ],
            'weekly_digest' => [
                'name' => 'Weekly Digest Newsletter',
                'type' => MarketingCampaign::TYPE_EMAIL,
                'template' => $emailTemplates->get('weekly_digest'),
                'status' => 'running',
                'settings' => [
                    'utm_campaign' => 'weekly-digest',
                    'content_blocks' => ['editorial', 'top_sellers', 'community_story'],
                ],
                'audience_filters' => [
                    'include_segments' => ['new_customers', 'vip_verified'],
                ],
                'scheduled_for' => now()->subDays(1),
                'last_dispatched_at' => now()->subDay(),
                'last_synced_at' => now()->subHours(2),
                'metrics' => [
                    'open_count' => 1280,
                    'click_count' => 412,
                    'conversion_count' => 96,
                ],
                'segments' => ['new_customers', 'vip_verified'],
                'schedule' => [
                    'cron_expression' => '0 8 * * MON',
                    'timezone' => config('app.timezone', 'UTC'),
                    'starts_at' => now()->subWeeks(3),
                    'ends_at' => now()->addWeeks(5),
                    'last_run_at' => now()->subDay(),
                    'next_run_at' => now()->addWeek(),
                    'is_active' => true,
                ],
            ],
            'winback_push' => [
                'name' => 'Win-back Push Journey',
                'type' => MarketingCampaign::TYPE_PUSH,
                'template' => $pushTemplates->get('winback_nudge'),
                'status' => 'completed',
                'settings' => [
                    'utm_campaign' => 'winback-push',
                    'reminder_window_days' => 14,
                ],
                'audience_filters' => [
                    'include_segments' => ['lapsed_accounts'],
                ],
                'scheduled_for' => now()->subWeeks(2),
                'last_dispatched_at' => now()->subWeek(),
                'last_synced_at' => now()->subDays(3),
                'metrics' => [
                    'open_count' => 640,
                    'click_count' => 205,
                    'conversion_count' => 62,
                ],
                'segments' => ['lapsed_accounts'],
                'schedule' => [
                    'cron_expression' => '*/30 * * * *',
                    'timezone' => 'UTC',
                    'starts_at' => now()->subWeeks(3),
                    'ends_at' => now()->subDays(2),
                    'last_run_at' => now()->subWeek(),
                    'next_run_at' => null,
                    'is_active' => false,
                ],
            ],
        ];

        $campaigns = collect();

        foreach ($campaignConfigs as $key => $config) {
            $template = $config['template'];

            if (! $template instanceof CampaignTemplate) {
                continue;
            }

            $campaign = MarketingCampaign::updateOrCreate(
                ['name' => $config['name']],
                [
                    'type' => $config['type'],
                    'template_id' => $template->id,
                    'status' => $config['status'],
                    'settings' => $config['settings'],
                    'audience_filters' => $config['audience_filters'],
                    'scheduled_for' => $config['scheduled_for'],
                    'last_dispatched_at' => $config['last_dispatched_at'],
                    'last_synced_at' => $config['last_synced_at'],
                    'open_count' => $config['metrics']['open_count'],
                    'click_count' => $config['metrics']['click_count'],
                    'conversion_count' => $config['metrics']['conversion_count'],
                ]
            );

            $segmentIds = $segments
                ->only($config['segments'])
                ->pluck('id')
                ->values()
                ->all();

            $campaign->segments()->sync($segmentIds);

            $schedule = $config['schedule'] ?? [];

            if (! empty($schedule)) {
                $campaign->schedule()->updateOrCreate([], $schedule);
            }

            $campaigns->put($key, $campaign->fresh(['template', 'segments', 'schedule']));
        }

        return $campaigns;
    }

    private function seedCampaignTests(Collection $campaigns, Collection $templates): void
    {
        $emailTemplates = $templates->get(MarketingCampaign::TYPE_EMAIL, collect());
        $pushTemplates = $templates->get(MarketingCampaign::TYPE_PUSH, collect());

        $testsConfig = [
            [
                'campaign' => $campaigns->get('weekly_digest'),
                'name' => 'Digest subject line test',
                'variant_a' => $emailTemplates->get('weekly_digest'),
                'variant_b' => $emailTemplates->get('loyalty_spotlight'),
                'traffic_split_a' => 60,
                'traffic_split_b' => 40,
                'status' => 'running',
                'metrics' => [
                    'opens' => 920,
                    'clicks' => 348,
                    'conversions' => 72,
                ],
                'winning' => null,
            ],
            [
                'campaign' => $campaigns->get('winback_push'),
                'name' => 'Win-back push copy test',
                'variant_a' => $pushTemplates->get('winback_nudge'),
                'variant_b' => $pushTemplates->get('flash_sale'),
                'traffic_split_a' => 50,
                'traffic_split_b' => 50,
                'status' => 'completed',
                'metrics' => [
                    'opens' => 470,
                    'clicks' => 168,
                    'conversions' => 51,
                ],
                'winning' => $pushTemplates->get('winback_nudge'),
            ],
        ];

        foreach ($testsConfig as $config) {
            $campaign = $config['campaign'];
            $variantA = $config['variant_a'];
            $variantB = $config['variant_b'];

            if (! $campaign || ! $variantA || ! $variantB) {
                continue;
            }

            CampaignTest::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'name' => $config['name'],
                ],
                [
                    'variant_a_template_id' => $variantA->id,
                    'variant_b_template_id' => $variantB->id,
                    'traffic_split_a' => $config['traffic_split_a'],
                    'traffic_split_b' => $config['traffic_split_b'],
                    'status' => $config['status'],
                    'metrics' => $config['metrics'],
                    'winning_template_id' => $config['winning']?->id,
                ]
            );
        }
    }

    private function seedLoyaltyTransactions(Collection $users, Collection $orders): void
    {
        $userIds = $users->except('admin')->pluck('id');
        LoyaltyPointTransaction::whereIn('user_id', $userIds)->delete();

        $entries = [
            [
                'factory' => LoyaltyPointTransaction::factory()->earn(180),
                'user' => $users->get('repeat'),
                'order' => $orders->get('paid'),
                'meta' => [
                    'key' => 'shop.api.orders.points_earned_description',
                    'number' => $orders->get('paid')?->number,
                ],
            ],
            [
                'factory' => LoyaltyPointTransaction::factory()->redeem(200),
                'user' => $users->get('repeat'),
                'order' => $orders->get('paid'),
                'meta' => [
                    'key' => 'shop.loyalty.demo.checkout_redeem',
                    'number' => $orders->get('paid')?->number,
                ],
            ],
            [
                'factory' => LoyaltyPointTransaction::factory()->earn(250),
                'user' => $users->get('vip'),
                'order' => $orders->get('shipped'),
                'meta' => [
                    'key' => 'shop.loyalty.demo.shipped_bonus',
                    'number' => $orders->get('shipped')?->number,
                ],
            ],
            [
                'factory' => LoyaltyPointTransaction::factory()->adjustment(-80),
                'user' => $users->get('buyer'),
                'order' => $orders->get('cancelled'),
                'meta' => [
                    'key' => 'shop.loyalty.demo.cancellation_return',
                ],
            ],
        ];

        foreach ($entries as $entry) {
            if (! $entry['user']) {
                continue;
            }

            $order = $entry['order'];
            $factory = $entry['factory']->for($entry['user']);
            if ($order) {
                $factory = $factory->for($order);
            }

            $meta = array_filter($entry['meta'], fn ($value) => $value !== null && $value !== '');
            $meta['key'] = $entry['meta']['key'];

            $factory->create([
                'description' => __($meta['key'], $meta),
                'meta' => $meta,
            ]);
        }
    }

    private function seedMessages(Collection $orders, Collection $users): void
    {
        $admin = $users->get('admin');
        $customerOrders = $orders->filter();

        Message::whereIn('order_id', $customerOrders->pluck('id'))->delete();

        foreach ($customerOrders as $order) {
            $buyer = $order->user;

            if ($buyer) {
                Message::factory()->for($order)->for($buyer)->create([
                    'body' => 'Hi! Could you confirm the delivery time for '.$order->number.'?',
                    'meta' => ['from' => 'customer'],
                ]);
            }

            if ($admin) {
                Message::factory()->for($order)->for($admin)->create([
                    'body' => 'Hello! Your order is being processed. Tracking will appear soon.',
                    'meta' => ['from' => 'manager'],
                ]);
            }
        }
    }

    private function seedReviewsAndWishlists(Collection $users): void
    {
        $customers = $users->except('admin');
        if ($customers->isEmpty()) {
            return;
        }

        $productSelection = Product::orderBy('id')->take($customers->count() * 3)->get();
        if ($productSelection->isEmpty()) {
            return;
        }

        Review::whereIn('user_id', $customers->pluck('id'))->delete();
        Wishlist::whereIn('user_id', $customers->pluck('id'))->delete();

        $statuses = [
            Review::STATUS_PENDING,
            Review::STATUS_APPROVED,
            Review::STATUS_REJECTED,
        ];

        $productCount = $productSelection->count();

        foreach ($statuses as $index => $status) {
            $user = $customers->values()[$index % $customers->count()];
            $product = $productSelection[$index % $productCount] ?? null;

            if (! $product) {
                continue;
            }

            Review::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => $user->id],
                [
                    'rating' => 3 + $index,
                    'text' => 'Demo review in status '.$status,
                    'status' => $status,
                ]
            );
        }

        if ($productSelection->isNotEmpty()) {
            $customers->values()->each(function (User $user, int $index) use ($productSelection) {
                $items = $productSelection->slice($index * 2, 2);

                if ($items->isEmpty()) {
                    $items = $productSelection->random(min(2, $productSelection->count()));
                }

                $items->each(function (Product $product) use ($user) {
                    Wishlist::updateOrCreate(
                        ['user_id' => $user->id, 'product_id' => $product->id],
                        []
                    );
                });
            });

            $productSelection->pluck('id')->unique()->each(function ($productId) {
                if ($product = Product::find($productId)) {
                    $product->refreshRating();
                }
            });
        }
    }
}
