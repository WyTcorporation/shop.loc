<?php

use App\Enums\ShipmentStatus;
use App\Mail\OrderDeliveredMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

it('does not send delivery email when shipment is delivered', function () {
    Mail::fake();

    $order = Order::factory()->create([
        'email' => 'customer@example.com',
    ]);

    $shipment = $order->shipment;

    $shipment->update(['status' => ShipmentStatus::Delivered]);

    Mail::assertNotSent(OrderDeliveredMail::class);
});
