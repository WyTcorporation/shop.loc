<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
//use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        $order = $this->order instanceof Order ? $this->order : Order::findOrFail($this->order);
        if (! $order->email) {
            return;
        }
        $mailable = new OrderPlacedMail($order);

        $pending = Mail::to($order->email);

        if ($admin = config('shop.admin_email')) {
            $pending->bcc($admin);
        }

        $pending->send($mailable);
    }

//    public function handle(): void
//    {
//        Mail::to($this->order->email)->send(new OrderPlacedMail($this->order));
//    }
}
