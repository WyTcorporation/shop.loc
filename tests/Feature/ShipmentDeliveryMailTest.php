<?php

use App\Enums\ShipmentStatus;
use App\Mail\OrderDeliveredMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

it('sends delivery email once when shipment is delivered', function () {
    Mail::fake();

    $order = Order::factory()->create([
        'email' => 'customer@example.com',
    ]);

    $shipment = $order->shipment;

    $shipment->update(['status' => ShipmentStatus::Delivered]);

    Mail::assertSent(OrderDeliveredMail::class, 1);

    Mail::assertSent(OrderDeliveredMail::class, function (OrderDeliveredMail $mail) use ($order) {
        return $mail->order->is($order);
    });
});
