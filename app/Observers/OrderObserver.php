<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\SendOrderStatusUpdate;
use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $from = $order->getOriginal('status');
        $to   = $order->status;

        if ($from === null) return;

        $order->logs()->create([
            'from_status' => $from,
            'to_status'   => $to,
            'changed_by'  => auth()->id(),
            'note'        => null,
        ]);


        if (in_array($to, [OrderStatus::Paid->value, OrderStatus::Shipped->value, OrderStatus::Cancelled->value], true)) {
            SendOrderStatusUpdate::dispatch($order, $from, $to);
        }
    }
}
