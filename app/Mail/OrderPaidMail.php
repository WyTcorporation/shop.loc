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
            ->subject("Замовлення {$this->order->number} оплачене")
            ->view('emails.orders.paid', ['order' => $this->order]);
    }
}
