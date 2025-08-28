<?php

use App\Enums\OrderStatus;
use App\Models\{Order, OrderItem, Product};

it('marks order paid and decrements stock', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 10]);
    $order   = Order::factory()->create(['status' => OrderStatus::New]);
    OrderItem::factory()->for($order)->for($product)->create(['qty' => 3, 'price' => 10]);

    $order->markPaid();
    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($product->refresh()->stock)->toBe(2);
});

it('cancels paid order and restocks', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 10]);
    $order   = Order::factory()->create(['status' => OrderStatus::New]);
    OrderItem::factory()->for($order)->for($product)->create(['qty' => 2, 'price' => 10]);

    $order->markPaid();
    $order->cancel();

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    expect($product->refresh()->stock)->toBe(5);
});
