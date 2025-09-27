<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\Mail\UserRoleTag;
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
        $locale = $this->locale ?: app()->getLocale();

        return $this->withLocale($locale, function () use ($locale) {
            $order = $this->order->loadMissing(['items.product', 'user']);
            $tag = UserRoleTag::for($order->user);

            return $this->subject(__('shop.orders.placed.subject_line', ['number' => $order->number], $locale))
                ->tag($tag)
                ->metadata([
                    'type' => 'order',
                    'mail_type' => 'order-placed',
                ])
                ->view('emails.orders.placed', [
                    'order' => $order,
                ]);
        });
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
