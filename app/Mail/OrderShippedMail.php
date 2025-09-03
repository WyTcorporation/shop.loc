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
            ->subject("Order {$this->order->number} is shipped")
            ->markdown('emails.orders.shipped', ['order' => $this->order]);
    }
}
