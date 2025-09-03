<?php

namespace App\Jobs;

use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status, // 'paid' | 'shipped' | ...
    ) {}

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (! $order || ! $order->email) {
            return;
        }

        match ($this->status) {
            'paid'    => Mail::to($order->email)->send(new OrderPaidMail($order)),
            'shipped' => Mail::to($order->email)->send(new OrderShippedMail($order)),
            default   => null,
        };
    }
}
