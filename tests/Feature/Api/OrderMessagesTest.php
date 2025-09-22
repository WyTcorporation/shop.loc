<?php

use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

it('allows an order owner to view their order messages', function () {
    $owner = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner, [], 'sanctum');

    getJson("/api/orders/{$order->id}/messages")
        ->assertOk();
});

it('forbids other customers from viewing order messages', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($other, [], 'sanctum');

    getJson("/api/orders/{$order->id}/messages")
        ->assertForbidden();
});
