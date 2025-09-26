<?php

namespace App\Services\Orders;

use App\Events\OrderMessageCreated;
use App\Models\Message;
use App\Models\Order;

class OrderMessageService
{
    public function create(Order $order, int|string $userId, string $body): Message
    {
        $message = $order->messages()->create([
            'user_id' => $userId,
            'body' => $body,
        ]);

        $message->loadMissing('user:id,name', 'order.user');

        OrderMessageCreated::dispatch($message);

        return $message;
    }
}
