<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

it('creates order from cart', function () {

    $product = Product::factory()->create([
        'stock' => 5,
        'price' => 10.50,
    ]);

    $cart = Cart::factory()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => $product->price,
    ]);

    $payload = [
        'cart_id' => $cart->id,
        'email' => 'test@example.com',
        'shipping_address' => ['name' => 'John', 'city' => 'Kyiv', 'addr' => 'Street 1'],
    ];

    $this->postJson('/api/orders', $payload)->assertCreated();
});
