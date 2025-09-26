<?php

namespace App\Services\Orders;

use App\Events\OrderMessageCreated;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

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

    public function markAsRead(Order $order, int|string $userId): void
    {
        $order->messages()
            ->whereNull('read_at')
            ->where(function (Builder $query) use ($userId) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', '!=', $userId);
            })
            ->update([
                'read_at' => now(),
                'read_by' => $userId,
            ]);
    }
}
