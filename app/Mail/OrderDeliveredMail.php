<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderDeliveredMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        $locale = $this->locale ?: app()->getLocale();

        return $this->withLocale($locale, function () use ($locale) {
            return $this
                ->subject(__('shop.orders.delivered.subject_line', ['number' => $this->order->number], $locale))
                ->tag('order-delivered')
                ->metadata(['type' => 'order'])
                ->view('emails.orders.delivered', [
                    'order' => $this->order->loadMissing(['shipment']),
                ]);
        });
    }
}
