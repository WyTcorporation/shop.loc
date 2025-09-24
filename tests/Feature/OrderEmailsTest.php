<?php

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

it('renders coupon summary in order placed email view', function () {
    $order = Order::factory()->create([
        'email' => 'customer@example.com',
        'coupon_code' => 'SAVE15',
        'coupon_discount' => 10,
        'loyalty_points_used' => 50,
        'loyalty_points_value' => 5,
    ]);

    $product = Product::factory()->create(['price' => 50]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 50,
    ]);

    $order->refresh();
    $order->recalculateTotal();
    $order->refresh();

    $html = (new OrderPlacedMail($order->loadMissing(['items.product'])))->render();

    expect($html)->toContain('Купон');
    expect($html)->toContain('SAVE15');
    expect($html)->toContain('Знижка');
    expect($html)->toContain('−15');
    expect($html)->toContain('Використані бали');
    expect($html)->toContain('50');
    expect($html)->toContain('−5');
    expect($html)->toContain('До сплати');
    expect($html)->toContain('85');
});
