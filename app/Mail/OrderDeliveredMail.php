<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\Mail\UserRoleTag;
use Illuminate\Mail\Mailable;

class OrderDeliveredMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        $locale = $this->locale ?: app()->getLocale();

        return $this->withLocale($locale, function () use ($locale) {
            $order = $this->order->loadMissing(['shipment', 'user']);
            $tag = UserRoleTag::for($order->user);

            return $this
                ->subject(__('shop.orders.delivered.subject_line', ['number' => $order->number], $locale))
                ->tag($tag)
                ->metadata([
                    'type' => 'order',
                    'mail_type' => 'order-delivered',
                ])
                ->view('emails.orders.delivered', [
                    'order' => $order,
                ]);
        });
    }
}
