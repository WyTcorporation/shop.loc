<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderShippedMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        return $this
            ->subject(__('shop.orders.shipped.subject_line', ['number' => $this->order->number]))
            ->tag('order-shipped')
            ->metadata(['type' => 'order'])
            ->view('emails.orders.shipped', ['order' => $this->order]);
    }
}
