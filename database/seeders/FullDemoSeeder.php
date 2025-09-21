<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\ShipmentStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Currency;
use App\Models\LoyaltyPointTransaction;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
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
