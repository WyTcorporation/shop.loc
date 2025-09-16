<?php


use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

beforeEach(function () {
    Product::factory()->count(5)->create();
});

it('lists products', function () {
    $this->getJson('/api/products')->assertOk()->assertJsonStructure([
        'data', 'current_page', 'per_page'
    ]);
});

it('filters products by search', function () {
    $this->getJson('/api/products?search=est')->assertOk();
});

it('creates order from cart flow (smoke)', function () {
    $product = Product::factory()->create([
        'price' => 12.34,
        'stock' => 5,
    ]);

    $cart = Cart::factory()->create(); // UUID

    CartItem::factory()->create([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'qty'        => 2,
        'price'      => $product->price,
    ]);

    $payload = [
        'cart_id' => $cart->id,
        'email'   => 'customer@example.com',
        'shipping_address' => ['name' => 'John', 'city' => 'Kyiv', 'addr' => 'Street 1'],
    ];

    $this->postJson('/api/orders', $payload)
        ->assertCreated()
        ->assertJsonPath('items.0.product_id', $product->id)
        ->assertJsonPath('shipment.status', 'pending');
});
