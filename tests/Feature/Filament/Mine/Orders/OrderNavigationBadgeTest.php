<?php

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

it('counts only orders involving the current vendor user in the navigation badge', function (): void {
    Permission::findOrCreate(PermissionEnum::ViewOrders->value, 'web');
    Permission::findOrCreate(PermissionEnum::ManageOrders->value, 'web');

    $user = User::factory()->create();
    $user->givePermissionTo([
        PermissionEnum::ViewOrders->value,
        PermissionEnum::ManageOrders->value,
    ]);

    $vendor = Vendor::factory()->for($user)->create();

    $visibleProduct = Product::factory()->for($vendor)->create();
    $hiddenProduct = Product::factory()->create();

    $now = now();

    $visibleOrderId = DB::table('orders')->insertGetId([
        'user_id' => null,
        'email' => 'visible@example.com',
        'status' => 'new',
        'total' => 0,
        'subtotal' => 0,
        'discount_total' => 0,
        'coupon_id' => null,
        'coupon_code' => null,
        'coupon_discount' => 0,
        'loyalty_points_used' => 0,
        'loyalty_points_value' => 0,
        'loyalty_points_earned' => 0,
        'shipping_address' => json_encode([
            'name' => 'Visible Buyer',
            'city' => 'Visible City',
            'addr' => 'Visible Street',
            'postal_code' => '00001',
            'phone' => '+380000000001',
        ], JSON_THROW_ON_ERROR),
        'billing_address' => null,
        'note' => null,
        'number' => 'ORD-' . Str::upper(Str::random(16)),
        'shipping_address_id' => null,
        'currency' => 'EUR',
        'payment_intent_id' => null,
        'payment_status' => 'pending',
        'paid_at' => null,
        'shipped_at' => null,
        'cancelled_at' => null,
        'inventory_committed_at' => null,
        'locale' => 'en',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $hiddenOrderId = DB::table('orders')->insertGetId([
        'user_id' => null,
        'email' => 'hidden@example.com',
        'status' => 'new',
        'total' => 0,
        'subtotal' => 0,
        'discount_total' => 0,
        'coupon_id' => null,
        'coupon_code' => null,
        'coupon_discount' => 0,
        'loyalty_points_used' => 0,
        'loyalty_points_value' => 0,
        'loyalty_points_earned' => 0,
        'shipping_address' => json_encode([
            'name' => 'Hidden Buyer',
            'city' => 'Hidden City',
            'addr' => 'Hidden Street',
            'postal_code' => '00002',
            'phone' => '+380000000002',
        ], JSON_THROW_ON_ERROR),
        'billing_address' => null,
        'note' => null,
        'number' => 'ORD-' . Str::upper(Str::random(16)),
        'shipping_address_id' => null,
        'currency' => 'EUR',
        'payment_intent_id' => null,
        'payment_status' => 'pending',
        'paid_at' => null,
        'shipped_at' => null,
        'cancelled_at' => null,
        'inventory_committed_at' => null,
        'locale' => 'en',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $visibleOrder = Order::findOrFail($visibleOrderId);
    $hiddenOrder = Order::findOrFail($hiddenOrderId);

    OrderItem::factory()->for($visibleOrder)->for($visibleProduct)->create();
    OrderItem::factory()->for($hiddenOrder)->for($hiddenProduct)->create();

    $this->actingAs($user);

    expect(OrderResource::getNavigationBadge())->toBe('1');

    $table = $visibleOrder->getTable();

    $visibleIds = OrderResource::getEloquentQuery()->pluck("{$table}.id")->all();

    expect($visibleIds)->toBe([$visibleOrder->getKey()]);
});
