<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $fromStatus,
        public string $toStatus,
    ) {}

    public function build(): self
    {
        return $this->subject(__('shop.orders.status_updated.subject_line', ['number' => $this->order->number]))
            ->markdown('emails.orders.status-updated', [
                'order'      => $this->order,
                'fromStatus' => $this->fromStatus,
                'toStatus'   => $this->toStatus,
            ]);
    }
}
