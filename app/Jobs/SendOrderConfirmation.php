<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $locale;

    public function __construct(public Order $order, ?string $locale = null)
    {
        $this->locale = $locale ?: app()->getLocale() ?: (string) config('app.locale');
    }

    public function handle(): void
    {
        $order = $this->order instanceof Order ? $this->order : Order::findOrFail($this->order);
        if (! $order->email) {
            return;
        }

        $previousLocale = app()->getLocale();
        app()->setLocale($this->locale);

        try {
            $mailable = (new OrderPlacedMail($order))->locale($this->locale);

            $pending = Mail::to($order->email)->locale($this->locale);

            if ($admin = config('shop.admin_email')) {
                $pending->bcc($admin);
            }

            $pending->send($mailable);
        } finally {
            app()->setLocale($previousLocale);
        }
    }

//    public function handle(): void
//    {
//        Mail::to($this->order->email)->send(new OrderPlacedMail($this->order));
//    }
}
