<?php

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Jobs\SendOrderStatusMail;
use App\Jobs\SendOrderStatusUpdate;
use App\Models\{Order, OrderItem, OrderStatusLog, Product, User};
use Illuminate\Support\Facades\Queue;

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

it('creates a status log and dispatches notifications when the order status changes', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create(['status' => OrderStatus::New]);

    $order->update(['status' => OrderStatus::Paid]);

    $log = OrderStatusLog::first();

    expect($log)->not->toBeNull();
    expect($log->order_id)->toBe($order->id);
    expect($log->from_status)->toBe(OrderStatus::New->value);
    expect($log->to_status)->toBe(OrderStatus::Paid->value);
    expect($log->changed_by)->toBe($user->id);
    expect($log->note)->toBeNull();

    Queue::assertPushed(SendOrderStatusMail::class, function (SendOrderStatusMail $job) use ($order) {
        return $job->orderId === $order->id
            && $job->status === OrderStatus::Paid->value;
    });

    Queue::assertPushed(SendOrderStatusUpdate::class, function (SendOrderStatusUpdate $job) use ($order) {
        return $job->order->is($order)
            && $job->toStatus === __('shop.orders.statuses.' . OrderStatus::Paid->value);
    });
});

it('records shipment status changes in the order log', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create();
    $shipment = $order->shipment;

    $shipment->update(['status' => ShipmentStatus::Delivered]);

    $log = OrderStatusLog::first();

    expect($log)->not->toBeNull();
    expect($log->order_id)->toBe($order->id);
    expect($log->from_status)->toBe(ShipmentStatus::Pending->value);
    expect($log->to_status)->toBe(ShipmentStatus::Delivered->value);
    expect($log->changed_by)->toBe($user->id);
    expect($log->note)->toBe(
        __('shop.orders.logs.shipment_status_note', [
            'status' => __('shop.orders.shipment_status.' . ShipmentStatus::Delivered->value),
        ])
    );

    Queue::assertPushed(SendOrderStatusMail::class, function (SendOrderStatusMail $job) use ($order) {
        return $job->orderId === $order->id
            && $job->status === ShipmentStatus::Delivered->value;
    });
});
