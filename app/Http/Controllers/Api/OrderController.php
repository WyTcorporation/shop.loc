<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderConfirmation;
use App\Models\{Cart, Order, OrderItem};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $r): JsonResponse
    {
        $data = $r->validate([
            'cart_id' => ['required', 'uuid', 'exists:carts,id'],
            'email' => ['required', 'email'],
            'shipping_address' => ['required', 'array'],
            'billing_address' => ['nullable', 'array'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = null;

        DB::transaction(function () use (&$order, $data) {
            $cart = Cart::with('items.product')->where('status', 'active')->findOrFail($data['cart_id']);
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
                'user_id' => $cart->user_id,
                'email' => $data['email'],
                'status' => 'new',
                'total' => $total,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? null,
                'note' => $data['note'] ?? null,
                'inventory_committed_at' => now()
            ]);


            $items = $cart->items->map(fn($it) => new OrderItem([
                'product_id' => $it->product_id,
                'qty' => $it->qty,
                'price' => $it->price,
            ]));
            $order->items()->saveMany($items);

            $cart->update(['status' => 'ordered']);
        });

        SendOrderConfirmation::dispatch($order);
        return response()->json($order->load('items'), 201);
    }

    public function show(string $number)
    {
        $order = Order::with([
            'items.product.images' => fn($q) => $q->orderBy('sort'),
        ])->where('number', $number)->firstOrFail();

        // підкласти preview_url для кожного item (якщо не збережений у таблиці)
        $items = $order->items->map(function ($it) {
            $preview = $it->preview_url
                ?? optional($it->product?->images?->firstWhere('is_primary', true))->url
                ?? optional($it->product?->images?->first())->url
                ?? $it->product?->preview_url;

            return array_merge($it->toArray(), [
                'preview_url' => $preview,
                'name'        => $it->name ?? $it->product?->name,
                'price'       => (float)($it->price ?? $it->product?->price ?? 0),
                'subtotal'    => (float)($it->subtotal ?? (($it->price ?? 0) * ($it->qty ?? 0))),
            ]);
        });

        $payload = $order->toArray();
        $payload['items'] = $items;

        return response()->json($payload);
    }
}
