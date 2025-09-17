<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\SendOrderConfirmation;
use App\Jobs\SendOrderStatusMail;
use App\Jobs\SendOrderStatusUpdate;
use App\Models\Order;
use BackedEnum;

class OrderObserver
{
    public bool $afterCommit = true;

    public function created(Order $order): void
    {
        SendOrderConfirmation::dispatch($order)->afterCommit();
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }


        $status = $order->getAttribute('status');
        $to = $status instanceof BackedEnum ? $status->value : (string) $status;

        SendOrderStatusMail::dispatch($order->getKey(), $to)->afterCommit();
    }
}
