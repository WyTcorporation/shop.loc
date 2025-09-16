<?php

use App\Enums\ShipmentStatus;
use App\Models\{Address, Cart, CartItem, Order, Product, Shipment};

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

    $response = $this->postJson('/api/orders', $payload)->assertCreated();

    $order = Order::with(['shipment', 'shippingAddress'])->first();

    expect($order)->not->toBeNull();
    expect($order->shipping_address_id)->not->toBeNull();
    expect($order->shipment)->not->toBeNull();
    expect($order->shipment->status)->toBeInstanceOf(ShipmentStatus::class);
    expect($order->shipment->status->value)->toBe(ShipmentStatus::Pending->value);

    expect(Address::count())->toBe(1);
    expect(Shipment::count())->toBe(1);

    $response->assertJsonPath('shipment.status', 'pending');
});
