<?php

use App\Models\{User, Product, Cart, CartItem};
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cookie;

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
