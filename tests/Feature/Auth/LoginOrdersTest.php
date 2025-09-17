<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('claims guest orders for the user upon login', function () {
    $password = 'secret-password';

    $user = User::factory()->create([
        'email' => 'customer@example.com',
        'password' => Hash::make($password),
    ]);

    $order = Order::factory()
        ->has(OrderItem::factory()->count(1), 'items')
        ->create([
            'email' => $user->email,
            'user_id' => null,
        ]);

    $shippingAddressId = $order->shipping_address_id;

    $login = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => $password,
    ])->assertOk()->json();

    expect($login)->toHaveKeys(['token', 'user']);

    $this->actingAs($user, 'sanctum');

    $ordersResponse = $this->getJson('/api/profile/orders?currency=USD')
        ->assertOk()
        ->json();

    expect($ordersResponse)->toHaveCount(1);
    expect($ordersResponse[0]['id'])->toBe($order->id);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('addresses', [
        'id' => $shippingAddressId,
        'user_id' => $user->id,
    ]);
});
