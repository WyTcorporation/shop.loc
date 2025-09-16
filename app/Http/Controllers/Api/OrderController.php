<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderConfirmation;
use App\Models\{Address, Cart, Coupon, LoyaltyPointTransaction, Order, OrderItem};
use App\Enums\ShipmentStatus;
use App\Services\Carts\CartPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $cart = Cart::with(['items.product', 'coupon', 'user'])
                ->where('status', 'active')
                ->findOrFail($data['cart_id']);

            if ($cart->items->isEmpty()) {
                abort(422, 'Cart is empty');
            }

            foreach ($cart->items as $item) {
                if ($item->product->stock < $item->qty) {
                    abort(422, "Insufficient stock for product #{$item->product_id}");
                }
            }

            $hadCoupon = (bool) $cart->coupon_id;
            $requestedPoints = (int) $cart->loyalty_points_used;

            $totals = CartPricingService::calculate($cart);

            if ($hadCoupon && ! $totals->coupon) {
                CartPricingService::syncCartAdjustments($cart, $totals);

                throw ValidationException::withMessages([
                    'coupon' => ['Coupon is no longer available.'],
                ]);
            }

            if ($requestedPoints > 0 && $totals->pointsUsed < $requestedPoints) {
                CartPricingService::syncCartAdjustments($cart, $totals);

                throw ValidationException::withMessages([
                    'points' => ['Not enough loyalty points to redeem the requested amount.'],
                ]);
            }

            CartPricingService::syncCartAdjustments($cart, $totals);

            foreach ($cart->items as $item) {
                $item->product()->decrement('stock', $item->qty);
            }

            $coupon = $totals->coupon
                ? Coupon::lockForUpdate()->find($totals->coupon->id)
                : null;

            if ($coupon) {
                if (! CartPricingService::couponIsApplicable($coupon, $cart, $totals->subtotal)) {
                    $cart->coupon()->dissociate();
                    $cart->coupon_code = null;
                    $cart->save();

                    throw ValidationException::withMessages([
                        'coupon' => ['Coupon is no longer available.'],
                    ]);
                }

                if ($coupon->usage_limit !== null && $coupon->used + 1 > $coupon->usage_limit) {
                    throw ValidationException::withMessages([
                        'coupon' => ['Coupon usage limit reached.'],
                    ]);
                }
            }

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

            $earnRate = max(0.0, (float) config('shop.loyalty.earn_rate', 1));
            $pointsEarned = $cart->user_id ? (int) floor($totals->total * $earnRate) : 0;

            $order = Order::create([
                'user_id' => $cart->user_id,
                'email' => $data['email'],
                'status' => 'new',
                'subtotal' => $totals->subtotal,
                'discount_total' => $totals->discountTotal(),
                'coupon_id' => $coupon?->id,
                'coupon_code' => $totals->couponCode,
                'coupon_discount' => $totals->couponDiscount,
                'loyalty_points_used' => $totals->pointsUsed,
                'loyalty_points_value' => $totals->pointsValue,
                'loyalty_points_earned' => $pointsEarned,
                'total' => $totals->total,
                'shipping_address' => $addressPayload,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address' => $data['billing_address'] ?? null,
                'note' => $data['note'] ?? null,
                'inventory_committed_at' => now(),
            ]);

            $items = $cart->items->map(fn ($it) => new OrderItem([
                'product_id' => $it->product_id,
                'qty' => $it->qty,
                'price' => $it->price,
            ]));
            $order->items()->saveMany($items);

            if ($coupon) {
                $coupon->increment('used');
            }

            if ($cart->user_id && $totals->pointsUsed > 0) {
                LoyaltyPointTransaction::create([
                    'user_id' => $cart->user_id,
                    'order_id' => $order->id,
                    'type' => LoyaltyPointTransaction::TYPE_REDEEM,
                    'points' => -$totals->pointsUsed,
                    'amount' => $totals->pointsValue,
                    'description' => 'Points redeemed for order ' . $order->number,
                ]);
            }

            if ($cart->user_id && $pointsEarned > 0) {
                LoyaltyPointTransaction::create([
                    'user_id' => $cart->user_id,
                    'order_id' => $order->id,
                    'type' => LoyaltyPointTransaction::TYPE_EARN,
                    'points' => $pointsEarned,
                    'amount' => $totals->total,
                    'description' => 'Points earned from order ' . $order->number,
                ]);
            }

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
