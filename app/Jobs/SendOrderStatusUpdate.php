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

    public string $locale;

    public function __construct(
        public Order $order,
        public ?string $fromStatus,
        public string $toStatus,
        ?string $locale = null,
    ) {
        $this->locale = $locale ?: app()->getLocale() ?: (string) config('app.locale');
    }

    public function handle(): void
    {
        if (! $this->order->email) return;

        $previousLocale = app()->getLocale();
        app()->setLocale($this->locale);

        try {
            Mail::to($this->order->email)
                ->locale($this->locale)
                ->send(
                    (new OrderStatusUpdatedMail(
                        $this->order,
                        $this->fromStatus,
                        $this->toStatus,
                        $this->locale
                    ))->locale($this->locale)
                );
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
