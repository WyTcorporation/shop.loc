<?php

use App\Models\{User, Product, Cart, CartItem};
use Illuminate\Auth\Events\Login;

it('attaches guest cart to user when user has no active cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

    $guest = Cart::factory()->create(['user_id' => null, 'status' => 'active']);
    CartItem::factory()->for($guest)->create(['product_id' => $product->id, 'qty' => 2, 'price' => 100]);

    $this->app['request']->cookies->set('cart_id', $guest->id);

    event(new Login('web', $user, false));

    $reloaded = Cart::query()->find($guest->id);
    expect($reloaded)->not->toBeNull();
    expect($reloaded->user_id)->toBe($user->id);
});

it('merges guest items into existing user cart with stock clamp', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5, 'price' => 10]);

    $userCart = Cart::factory()->create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::factory()->for($userCart)->create(['product_id' => $product->id, 'qty' => 2, 'price' => 10]);

    $guest = Cart::factory()->create(['user_id' => null, 'status' => 'active']);
    CartItem::factory()->for($guest)->create(['product_id' => $product->id, 'qty' => 4, 'price' => 10]);

    $this->app['request']->cookies->set('cart_id', $guest->id);

    event(new Login('web', $user, false));

    $mergedItem = $userCart->items()->where('product_id', $product->id)->first();
    expect($mergedItem->qty)->toBe(5);
});

it('keeps reassigned guest cart active and retrievable via getOrCreate', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 99]);

    $guest = Cart::factory()->create(['user_id' => null, 'status' => 'active']);
    CartItem::factory()->for($guest)->create([
        'product_id' => $product->id,
        'qty' => 3,
        'price' => 99,
    ]);

    $this->app['request']->cookies->set('cart_id', $guest->id);

    event(new Login('web', $user, false));

    $guest->refresh();
    expect($guest->status)->toBe('active');
    expect($guest->user_id)->toBe($user->id);

    $this->actingAs($user);

    $response = $this->getJson('/api/cart')->assertOk();

    $response->assertJsonPath('id', $guest->id);
    $response->assertJsonPath('status', 'active');
    $response->assertJsonCount(1, 'items');
    $response->assertJsonPath('items.0.product_id', $product->id);
    $response->assertJsonPath('items.0.qty', 3);
});
