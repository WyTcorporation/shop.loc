<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\Mail\UserRoleTag;
use Illuminate\Mail\Mailable;

class OrderShippedMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        $locale = $this->locale ?: app()->getLocale();

        return $this->withLocale($locale, function () use ($locale) {
            $order = $this->order->loadMissing(['user']);
            $tag = UserRoleTag::for($order->user);

            return $this
                ->subject(__('shop.orders.shipped.subject_line', ['number' => $order->number], $locale))
                ->tag($tag)
                ->metadata([
                    'type' => 'order',
                    'mail_type' => 'order-shipped',
                ])
                ->view('emails.orders.shipped', ['order' => $order]);
        });
    }
}
