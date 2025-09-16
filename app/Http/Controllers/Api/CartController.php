<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cart, CartItem, Coupon, Product};
use App\Services\Carts\CartPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function getOrCreate(Request $request): JsonResponse
    {
        $id = $request->cookie('cart_id');
        $cart = $id
            ? Cart::active()->with(['items.product', 'coupon', 'user'])->find($id)
            : null;

        if (! $cart) {
            $cart = Cart::create(['user_id' => auth()->id()]);
            $cart->load(['items.product', 'coupon', 'user']);
        }

        return $this->cartResponse($cart)
            ->cookie('cart_id', $cart->id, 60 * 24 * 30, '/', null, false, false, 'Lax');
    }

    public function show(string $id): JsonResponse
    {
        $cart = Cart::with(['items.product', 'coupon', 'user'])->findOrFail($id);

        return $this->cartResponse($cart);
    }

    public function addItem(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $qty = (int) ($data['qty'] ?? 1);

        $cart = Cart::active()->with(['items.product', 'coupon', 'user'])->findOrFail($id);

        return DB::transaction(function () use ($cart, $data, $qty) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            if ($qty > (int) $product->stock) {
                return response()->json(['message' => 'Not enough stock'], 422);
            }

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $newQty = min($item->qty + $qty, (int) $product->stock);
                $item->update([
                    'qty' => $newQty,
                    'price' => $product->price,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $product->price,
                ]);
            }

            $cart->load(['items.product', 'coupon', 'user']);

            return $this->cartResponse($cart);
        });
    }

    public function updateItem(Request $request, string $id, CartItem $item): JsonResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        if ($item->cart_id !== $id) {
            abort(404);
        }

        return DB::transaction(function () use ($data, $item) {
            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            $qty = min((int) $data['qty'], max(0, (int) $product->stock));
            if ($qty === 0) {
                $cartId = $item->cart_id;
                $item->delete();
                $cart = Cart::with(['items.product', 'coupon', 'user'])->findOrFail($cartId);

                return $this->cartResponse($cart);
            }

            $item->update([
                'qty' => $qty,
                'price' => $product->price,
            ]);

            $cart = Cart::with(['items.product', 'coupon', 'user'])->findOrFail($item->cart_id);

            return $this->cartResponse($cart);
        });
    }

    public function removeItem(string $id, CartItem $item): JsonResponse
    {
        if ($item->cart_id !== $id) {
            abort(404);
        }

        $item->delete();
        $cart = Cart::with(['items.product', 'coupon', 'user'])->findOrFail($id);

        return $this->cartResponse($cart);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cart_id' => ['required', 'uuid', 'exists:carts,id'],
            'code' => ['nullable', 'string'],
        ]);

        $cart = Cart::active()->with(['items.product', 'coupon', 'user'])->findOrFail($data['cart_id']);

        if (blank($data['code'])) {
            $cart->coupon()->dissociate();
            $cart->coupon_code = null;
            $cart->save();
            $cart->setRelation('coupon', null);

            return $this->cartResponse($cart);
        }

        $code = Str::upper($data['code']);
        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => ['Coupon not found.'],
            ]);
        }

        $totals = CartPricingService::calculate($cart, couponOverride: $coupon);

        if (! $totals->coupon) {
            throw ValidationException::withMessages([
                'code' => ['Coupon cannot be applied to this cart.'],
            ]);
        }

        CartPricingService::syncCartAdjustments($cart, $totals);

        return $this->cartResponse($cart);
    }

    public function applyPoints(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cart_id' => ['required', 'uuid', 'exists:carts,id'],
            'points' => ['required', 'integer', 'min:0'],
        ]);

        $cart = Cart::active()->with(['items.product', 'coupon', 'user'])->findOrFail($data['cart_id']);

        if (! $cart->user_id) {
            throw ValidationException::withMessages([
                'points' => ['Only authenticated users can redeem loyalty points.'],
            ]);
        }

        $totals = CartPricingService::calculate($cart, overridePoints: (int) $data['points']);
        CartPricingService::syncCartAdjustments($cart, $totals);

        return $this->cartResponse($cart);
    }

    private function cartResponse(Cart $cart): JsonResponse
    {
        $cart->loadMissing(['items.product', 'coupon', 'user']);

        $items = $cart->items->map(function (CartItem $item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name,
                'slug' => $item->product?->slug,
                'image' => $item->product?->preview_url,
                'price' => (float) $item->price,
                'qty' => (int) $item->qty,
                'line_total' => (float) $item->price * (int) $item->qty,
            ];
        })->values();

        $totals = CartPricingService::calculate($cart);
        CartPricingService::syncCartAdjustments($cart, $totals);

        return response()->json([
            'id' => $cart->id,
            'status' => $cart->status,
            'items' => $items,
            'subtotal' => $totals->subtotal,
            'discounts' => [
                'coupon' => [
                    'code' => $totals->couponCode,
                    'amount' => $totals->couponDiscount,
                ],
                'loyalty_points' => [
                    'used' => $totals->pointsUsed,
                    'value' => $totals->pointsValue,
                ],
                'total' => $totals->discountTotal(),
            ],
            'total' => $totals->total,
            'available_points' => $totals->availablePoints,
            'max_redeemable_points' => $totals->maxRedeemablePoints,
        ]);
    }
}
