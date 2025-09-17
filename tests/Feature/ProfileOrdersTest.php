<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('returns the current user orders with related data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Order::factory()
        ->count(2)
        ->for($user)
        ->has(OrderItem::factory()->count(2), 'items')
        ->create([
            'subtotal' => 150,
            'total' => 200,
        ]);

    Order::factory()
        ->for($otherUser)
        ->has(OrderItem::factory()->count(1), 'items')
        ->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/profile/orders?currency=USD')
        ->assertOk()
        ->json();

    expect($response)->toBeArray();
    expect($response)->toHaveCount(2);
    expect(collect($response)->pluck('user_id')->unique()->all())->toBe([$user->id]);
    expect(collect($response)->pluck('currency')->unique()->all())->toBe(['USD']);

    $firstOrder = $response[0];
    expect($firstOrder['items'])->not()->toBeEmpty();
    expect($firstOrder['shipment'])->not()->toBeNull();

    $firstItem = $firstOrder['items'][0];
    expect($firstItem['product'])->not()->toBeNull();
    expect($firstItem['product']['vendor'])->not()->toBeNull();
});

it('denies access to orders list for guests', function () {
    $this->getJson('/api/profile/orders')->assertUnauthorized();
});
