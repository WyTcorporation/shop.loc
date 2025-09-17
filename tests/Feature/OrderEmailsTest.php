<?php

use App\Jobs\SendOrderConfirmation;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use App\Enums\OrderStatus;
use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;

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

    Mail::assertSent(OrderPlacedMail::class, function (OrderPlacedMail $mail) use ($order) {
        $mail->assertHasTag('order-placed')->assertHasMetadata('type', 'order');

        return $mail->order->is($order);
    });
});


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



it('sends paid and shipped emails on status change', function () {
    Mail::fake();

    $p = Product::factory()->create(['price' => 10, 'stock' => 5]);
    $o = Order::factory()->create(['email' => 'customer@example.com', 'status' => OrderStatus::New->value]);
    OrderItem::factory()->for($o)->create(['product_id' => $p->id, 'qty' => 2, 'price' => 10]);

    // mark paid
    $o->update(['status' => OrderStatus::Paid->value]);
    Mail::assertSent(OrderPaidMail::class, function (OrderPaidMail $mail) {
        $mail->assertHasTag('order-paid')->assertHasMetadata('type', 'order');

        return true;
    });

    // mark shipped
    $o->update(['status' => OrderStatus::Shipped->value]);
    Mail::assertSent(OrderShippedMail::class, function (OrderShippedMail $mail) {
        $mail->assertHasTag('order-shipped')->assertHasMetadata('type', 'order');

        return true;
    });
});
