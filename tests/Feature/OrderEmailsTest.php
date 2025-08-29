<?php

use App\Jobs\SendOrderConfirmation;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

it('sends order placed email', function () {
    Mail::fake();

    $order = Order::factory()->create([
        'email' => 'customer@example.com',
        'total' => 123.45,
    ]);

    $p = Product::factory()->create(['price' => 12.34]);
    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $p->id,
        'qty'        => 2,
        'price'      => 12.34,
    ]);

    SendOrderConfirmation::dispatchSync($order);

    Mail::assertSent(OrderPlacedMail::class, function ($m) use ($order) {
        return $m->order->is($order);
    });
});
