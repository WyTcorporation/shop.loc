<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderPaidMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        return $this
            ->subject(__('shop.orders.paid.subject_line', ['number' => $this->order->number]))
            ->tag('order-paid')
            ->metadata(['type' => 'order'])
            ->view('emails.orders.paid', ['order' => $this->order]);
    }
}
