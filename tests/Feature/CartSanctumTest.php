<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('assigns sanctum authenticated user to the cart and resulting order', function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ]]);

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 5,
        'price' => 199.99,
    ]);

    Sanctum::actingAs($user, [], 'sanctum');

    $cartId = getJson('/api/cart')
        ->assertOk()
        ->json('id');

    expect($cartId)->not->toBeNull();

    $cart = Cart::query()->findOrFail($cartId);
    expect((string) $cart->user_id)->toBe((string) $user->id);

    postJson("/api/cart/{$cartId}/items", [
        'product_id' => $product->id,
        'qty' => 1,
    ])->assertOk();

    $orderResponse = postJson('/api/orders', [
        'cart_id' => $cartId,
        'email' => $user->email,
        'shipping_address' => [
            'name' => 'Test User',
            'city' => 'Kyiv',
            'addr' => 'Main street, 1',
            'postal_code' => '01001',
            'phone' => '+380000000000',
        ],
        'billing_address' => null,
        'note' => null,
    ])->assertCreated();

    $order = Order::query()->first();

    expect($order)->not->toBeNull();
    expect((string) $order->user_id)->toBe((string) $user->id);

    $orderResponse->assertJsonPath('user_id', $user->id);
});
