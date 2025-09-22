<?php

use App\Enums\Permission as PermissionEnum;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use Spatie\Permission\Models\Permission;

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

it('allows an order owner to post messages to their order', function () {
    $owner = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner, [], 'sanctum');

    postJson("/api/orders/{$order->id}/messages", [
        'body' => 'Hello from the customer!',
    ])->assertCreated();
});

it('forbids other customers from posting messages to an order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $owner->id,
    ]);

    Permission::findOrCreate(PermissionEnum::ManageOrders->value);

    Sanctum::actingAs($other, [], 'sanctum');

    postJson("/api/orders/{$order->id}/messages", [
        'body' => 'I should not be able to post this.',
    ])->assertForbidden();
});
