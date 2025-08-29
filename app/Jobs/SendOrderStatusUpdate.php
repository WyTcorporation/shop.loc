<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $fromStatus,
        public string $toStatus,
    ) {}

    public function handle(): void
    {
        if (! $this->order->email) return;

        Mail::to($this->order->email)->send(
            new OrderStatusUpdatedMail($this->order, $this->fromStatus, $this->toStatus)
        );
    }
}
