<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class MergeGuestCart
{
    public function handle(Login $event): void
    {
        // Акуратно дістаємо cart_id з доступних джерел (у т.ч. коли немає повного HTTP-запиту)
        $req = app()->bound('request') ? app('request') : null;

        $guestCartId =
            ($req?->cookie('cart_id'))
            ?? ($req?->cookies?->get('cart_id'))
            ?? Cookie::get('cart_id')
            ?? session('cart_id');

        if (!$guestCartId) {
            return;
        }

        $user = $event->user;

        DB::transaction(function () use ($user, $guestCartId) {
            /** @var Cart|null $guest */
            $guest = Cart::query()
                ->whereKey($guestCartId)
                ->where('status', 'active')
                ->with(['items' => fn ($q) => $q->orderBy('id')])
                ->lockForUpdate()
                ->first();

            if (!$guest) {
                Cookie::queue(Cookie::forget('cart_id'));
                return;
            }

            if ($guest->user_id && (string) $guest->user_id === (string) $user->id) {
                Cookie::queue(Cookie::forget('cart_id'));
                return;
            }

            /** @var Cart|null $userCart */
            $userCart = Cart::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

// 1) Немає активного кошика у користувача — просто “прикріплюємо” guest.
            if (! $userCart) {
                $guest->forceFill([
                    'status'  => 'active',      // залишаємо кошик активним для подальших запитів
                    'user_id' => $user->id,     // фіксуємо власника активного кошика
                ])->saveQuietly();
                Cookie::queue(Cookie::forget('cart_id'));
                return; // критично, щоб не дійти до видалення guest
            }

// 2) Є активний, але ПУСТИЙ — віддаємо перевагу guest-кошикові.
            if ($userCart->items()->doesntExist()) {
                // можна видалити або “заархівувати” порожній, обери політику:
                $userCart->delete(); // якщо SoftDeletes — це буде soft-delete

                $guest->forceFill(['user_id' => $user->id])->saveQuietly();
                Cookie::queue(Cookie::forget('cart_id'));
                return;
            }

// 3) Інакше — це справжній мердж (користувач уже щось мав у кошику).
            $userCartItems = $userCart->items()->get()->keyBy('product_id');

            /** @var CartItem $gItem */
            foreach ($guest->items as $gItem) {
                /** @var Product|null $product */
                $product = Product::query()->lockForUpdate()->find($gItem->product_id);
                if (! $product) {
                    continue;
                }

                $existing = $userCartItems->get($gItem->product_id);

                $targetQty  = ($existing?->qty ?? 0) + $gItem->qty;
                $clampedQty = min($targetQty, max(0, (int) $product->stock));

                if ($existing) {
                    $existing->update([
                        'qty'   => $clampedQty,
                        'price' => $product->price,
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_id' => $gItem->product_id,
                        'qty'        => $clampedQty,
                        'price'      => $product->price,
                    ]);
                }
            }


            $guest->items()->delete();
            $guest->delete();

            Cookie::queue(Cookie::forget('cart_id'));

        });
    }
}
