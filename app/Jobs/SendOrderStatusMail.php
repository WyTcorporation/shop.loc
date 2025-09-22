<?php

namespace App\Jobs;

use App\Mail\OrderDeliveredMail;
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

    public string $locale;

    public function __construct(
        public int $orderId,
        public string $status, // 'paid' | 'shipped' | ...
        ?string $locale = null,
    ) {
        $this->locale = $locale ?: app()->getLocale() ?: (string) config('app.locale');
    }

    public function handle(): void
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->locale);

        try {
            $order = Order::find($this->orderId);
            if (! $order || ! $order->email) {
                return;
            }

            $pending = Mail::to($order->email)->locale($this->locale);

            $mailable = match ($this->status) {
                'paid'      => new OrderPaidMail($order),
                'shipped'   => new OrderShippedMail($order),
                'delivered' => new OrderDeliveredMail($order),
                default     => null,
            };

            if ($mailable) {
                $pending->send($mailable->locale($this->locale));
            }
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
