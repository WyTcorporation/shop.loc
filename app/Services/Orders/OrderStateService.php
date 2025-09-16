<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use DomainException;

class OrderStateService
{
    public function markPaid(Order $order): void
    {
        if ($order->status !== OrderStatus::New) {
            throw new DomainException('Only "new" orders can be marked as paid.');
        }

        DB::transaction(function () use ($order) {
            $order->reserveInventory();
            $order->update(['status' => OrderStatus::Paid]);
        });
    }

    public function markShipped(Order $order): void
    {
        if ($order->status !== OrderStatus::Paid) {
            throw new DomainException('Only "paid" orders can be marked as shipped.');
        }

        DB::transaction(function () use ($order) {
            $order->commitReservedInventory();
            $order->update(['status' => OrderStatus::Shipped]);
        });
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        if (!in_array($order->status, [OrderStatus::New, OrderStatus::Paid], true)) {
            throw new DomainException('Only "new" or "paid" orders can be cancelled.');
        }

        DB::transaction(function () use ($order) {
            $order->releaseInventory();

            $order->update(['status' => OrderStatus::Cancelled]);
        });
    }
}
