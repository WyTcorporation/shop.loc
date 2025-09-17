<?php

namespace App\Listeners;

use App\Models\Order;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class ClaimGuestOrders
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user || ! $user->email) {
            return;
        }

        DB::transaction(function () use ($user) {
            $orders = Order::query()
                ->whereNull('user_id')
                ->where('email', $user->email)
                ->with('shippingAddress')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return;
            }

            foreach ($orders as $order) {
                $order->forceFill(['user_id' => $user->id])->saveQuietly();

                $shippingAddress = $order->shippingAddress;

                if ($shippingAddress && $shippingAddress->user_id === null) {
                    $shippingAddress->forceFill(['user_id' => $user->id])->saveQuietly();
                }
            }
        });
    }
}
