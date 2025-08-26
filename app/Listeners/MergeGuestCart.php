<?php

namespace App\Listeners;

use App\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class MergeGuestCart
{
    public function __construct(private Request $request)
    {
    }

    public function handle(Login $event): void
    {
        $cookieCartId = $this->request->cookie('cart_id');
        if (!$cookieCartId) return;

        $guest = Cart::query()->whereKey($cookieCartId)->first();
        if (!$guest || $guest->user_id) return;

        $userCart = Cart::query()->firstOrCreate(['user_id' => $event->user->id]);
        if ($guest->id === $userCart->id) return;

        foreach ($guest->items as $it) {
            $existing = $userCart->items()
                ->where('product_id', $it->product_id)
                ->first();

            if ($existing) {
                $existing->increment('qty', $it->qty);
            } else {
                $userCart->items()->create([
                    'product_id' => $it->product_id,
                    'qty' => $it->qty,
                    'price' => $it->price,
                ]);
            }
        }

        $guest->delete();
    }
}
