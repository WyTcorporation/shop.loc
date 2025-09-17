<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderDeliveredMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        return $this
            ->subject("Order {$this->order->number} is delivered")
            ->markdown('emails.orders.delivered', ['order' => $this->order]);
    }
}
