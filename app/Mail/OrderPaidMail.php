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
            ->subject("Order {$this->order->number} is paid")
            ->markdown('emails.orders.paid', ['order' => $this->order]);
    }
}
