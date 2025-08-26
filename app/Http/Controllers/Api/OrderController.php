<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderConfirmation;
use App\Models\{Cart, Order, OrderItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'cart_id'          => ['required','uuid','exists:carts,id'],
            'email'            => ['required','email'],
            'shipping_address' => ['required','array'],
            'billing_address'  => ['nullable','array'],
            'note'             => ['nullable','string','max:2000'],
        ]);

        $order = null;

        DB::transaction(function () use (&$order, $data) {
            $cart = Cart::with('items.product')->where('status','active')->findOrFail($data['cart_id']);
            if ($cart->items->isEmpty()) {
                abort(422, 'Cart is empty');
            }

            // Перевірка складу
            foreach ($cart->items as $it) {
                if ($it->product->stock < $it->qty) {
                    abort(422, "Insufficient stock for product #{$it->product_id}");
                }
            }

            // Декремент складу (простий варіант без резервування)
            foreach ($cart->items as $it) {
                $it->product()->decrement('stock', $it->qty);
            }


            $total = $cart->items->sum(fn($it) => $it->qty * (float)$it->price);

            $order = Order::create([
                'user_id'         => $cart->user_id,
                'email'           => $data['email'],
                'status'          => 'new',
                'total'           => $total,
                'shipping_address'=> $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? null,
                'note'            => $data['note'] ?? null,
            ]);


            $items = $cart->items->map(fn($it) => new OrderItem([
                'product_id' => $it->product_id,
                'qty'        => $it->qty,
                'price'      => $it->price,
            ]));
            $order->items()->saveMany($items);

            $cart->update(['status' => 'ordered']);
        });

        SendOrderConfirmation::dispatch($order);
        return response()->json($order->load('items'), 201);
    }
}
