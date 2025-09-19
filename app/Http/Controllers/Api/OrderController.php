<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Address, Cart, Coupon, LoyaltyPointTransaction, Order, OrderItem, Warehouse};
use App\Enums\ShipmentStatus;
use App\Services\Carts\CartPricingService;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use DomainException;

class OrderController extends Controller
{
    public function __construct(private CurrencyConverter $converter)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, __('shop.api.auth.unauthenticated'));
        }

        $currency = $this->resolveCurrency($request);

        $orders = Order::with([
            'items.product.images' => fn ($query) => $query->orderBy('sort'),
            'items.product.vendor',
            'shipment',
        ])->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        $payload = $orders
            ->map(fn (Order $order) => $this->transformOrder($order, $currency))
            ->values()
            ->all();

        return response()->json($payload);
    }

    public function store(Request $r): JsonResponse
    {
        $currency = $this->resolveCurrency($r);

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
                abort(422, __('shop.api.orders.cart_empty'));
            }

            $warehouse = Warehouse::getDefault();

            foreach ($cart->items as $item) {
                if ($item->product->availableStock($warehouse->id) < $item->qty) {
                    abort(422, __('shop.api.orders.insufficient_stock', ['product' => $item->product_id]));
                }
            }

            $hadCoupon = (bool) $cart->coupon_id;
            $requestedPoints = (int) $cart->loyalty_points_used;

            $totals = CartPricingService::calculate($cart);

            if ($hadCoupon && ! $totals->coupon) {
                CartPricingService::syncCartAdjustments($cart, $totals);

                throw ValidationException::withMessages([
                    'coupon' => [__('shop.api.orders.coupon_unavailable')],
                ]);
            }

            if ($requestedPoints > 0 && $totals->pointsUsed < $requestedPoints) {
                CartPricingService::syncCartAdjustments($cart, $totals);

                throw ValidationException::withMessages([
                    'points' => [__('shop.api.orders.not_enough_points')],
                ]);
            }

            CartPricingService::syncCartAdjustments($cart, $totals);

            foreach ($cart->items as $item) {
                try {
                    $item->product->reserveStock($item->qty, $warehouse->id);
                } catch (DomainException $e) {
                    abort(422, __('shop.api.orders.insufficient_stock', ['product' => $item->product_id]));
                }
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
                        'coupon' => [__('shop.api.orders.coupon_unavailable')],
                    ]);
                }

                if ($coupon->usage_limit !== null && $coupon->used + 1 > $coupon->usage_limit) {
                    throw ValidationException::withMessages([
                        'coupon' => [__('shop.api.orders.coupon_usage_limit_reached')],
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
                'currency' => $this->converter->getBaseCurrency(),
            ]);

            $items = $cart->items->map(fn ($it) => new OrderItem([
                'product_id' => $it->product_id,
                'warehouse_id' => $warehouse->id,
                'qty' => $it->qty,
                'price' => $it->price,
            ]));
            $order->items()->saveMany($items);

            if ($coupon) {
                $coupon->increment('used');
            }

            if ($cart->user_id && $totals->pointsUsed > 0) {
                $meta = [
                    'key' => 'shop.api.orders.points_redeemed_description',
                    'number' => $order->number,
                ];

                LoyaltyPointTransaction::create([
                    'user_id' => $cart->user_id,
                    'order_id' => $order->id,
                    'type' => LoyaltyPointTransaction::TYPE_REDEEM,
                    'points' => -$totals->pointsUsed,
                    'amount' => $totals->pointsValue,
                    'description' => __($meta['key'], $meta),
                    'meta' => $meta,
                ]);
            }

            if ($cart->user_id && $pointsEarned > 0) {
                $meta = [
                    'key' => 'shop.api.orders.points_earned_description',
                    'number' => $order->number,
                ];

                LoyaltyPointTransaction::create([
                    'user_id' => $cart->user_id,
                    'order_id' => $order->id,
                    'type' => LoyaltyPointTransaction::TYPE_EARN,
                    'points' => $pointsEarned,
                    'amount' => $totals->total,
                    'description' => __($meta['key'], $meta),
                    'meta' => $meta,
                ]);
            }

            $cart->update(['status' => 'ordered']);

            $order->shipment()->create([
                'address_id' => $shippingAddress->id,
                'status' => ShipmentStatus::Pending,
            ]);
        });

        $order->load('items.product.images', 'items.product.vendor', 'shipment');

        return response()->json($this->transformOrder($order, $currency), 201);
    }

    public function show(Request $request, string $number)
    {
        $currency = $this->resolveCurrency($request);

        $order = Order::with([
            'items.product.images' => fn($q) => $q->orderBy('sort'),
            'items.product.vendor',
            'shipment',
        ])->where('number', $number)->firstOrFail();

        return response()->json($this->transformOrder($order, $currency));
    }

    private function transformOrder(Order $order, string $currency): array
    {
        $baseCurrency = $this->converter->getBaseCurrency();

        $items = $order->items->map(function (OrderItem $item) use ($currency) {
            $preview = $item->preview_url
                ?? optional($item->product?->images?->firstWhere('is_primary', true))->url
                ?? optional($item->product?->images?->first())->url
                ?? $item->product?->preview_url;

            $basePrice = (float) ($item->price ?? $item->product?->price ?? 0);
            $convertedPrice = $this->converter->convertFromBase($basePrice, $currency);

            $qty = (int) ($item->qty ?? 0);
            $baseSubtotal = $basePrice * $qty;
            $convertedSubtotal = $this->converter->convertFromBase($baseSubtotal, $currency);

            return array_merge($item->toArray(), [
                'preview_url' => $preview,
                'name' => $item->name ?? $item->product?->name,
                'price' => $convertedPrice,
                'subtotal' => round($convertedSubtotal, 2),
            ]);
        });

        $payload = $order->toArray();

        foreach (['subtotal', 'discount_total', 'total', 'coupon_discount', 'loyalty_points_value'] as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $value = $order->{$field};

            if ($value === null) {
                $payload[$field] = null;
                continue;
            }

            $payload[$field] = $this->converter->convertFromBase((float) $value, $currency);
        }

        $payload['currency'] = $currency;
        $payload['base_currency'] = $baseCurrency;
        $payload['items'] = $items->all();

        return $payload;
    }

    private function resolveCurrency(Request $request): string
    {
        $routeCurrency = $request->route('currency');
        $queryCurrency = $request->query('currency');

        return $this->converter->normalizeCurrency($routeCurrency ?? $queryCurrency);
    }
}
