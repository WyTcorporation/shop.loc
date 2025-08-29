<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        return $this->subject("Ваше замовлення #{$this->order->number} прийнято")
            ->markdown('emails.orders.placed', [
                'order' => $this->order->loadMissing(['items.product']),
            ]);
    }

//    public function envelope(): Envelope
//    {
//        return new Envelope(
//            subject: 'Order ' . $this->order->number . ' confirmed',
//        );
//    }
//
//    public function content(): Content
//    {
//        return new Content(
//            markdown: 'emails.orders.placed',
//            with: ['order' => $this->order],
//        );
//    }
//
//    public function attachments(): array
//    {
//        return [];
//    }
}
