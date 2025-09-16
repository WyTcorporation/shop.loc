<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderConfirmation;
use App\Models\{Address, Cart, Order, OrderItem};
use App\Enums\ShipmentStatus;
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
            'shipping_address.name' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.addr' => ['required', 'string', 'max:500'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:32'],
            'shipping_address.phone' => ['nullable', 'string', 'max:32'],
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

            $addressPayload = [
                'name' => $data['shipping_address']['name'],
                'city' => $data['shipping_address']['city'],
                'addr' => $data['shipping_address']['addr'],
                'postal_code' => $data['shipping_address']['postal_code'] ?? null,
                'phone' => $data['shipping_address']['phone'] ?? null,
            ];

            $addressAttributes = array_merge([
                'user_id' => $cart->user_id,
            ], $addressPayload);

            $shippingAddress = $cart->user_id
                ? Address::firstOrCreate($addressAttributes)
                : Address::create($addressAttributes);

            $order = Order::create([
                'user_id' => $cart->user_id,
                'email' => $data['email'],
                'status' => 'new',
                'total' => $total,
                'shipping_address' => $addressPayload,
                'shipping_address_id' => $shippingAddress->id,
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

            $order->shipment()->create([
                'address_id' => $shippingAddress->id,
                'status' => ShipmentStatus::Pending,
            ]);
        });

        SendOrderConfirmation::dispatch($order);
        return response()->json($order->load('items', 'shipment'), 201);
    }

    public function show(string $number)
    {
        $order = Order::with([
            'items.product.images' => fn($q) => $q->orderBy('sort'),
            'shipment',
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
