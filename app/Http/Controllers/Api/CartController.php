<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\{Cart, CartItem, Product};

class CartController extends Controller
{
    public function getOrCreate(Request $r): JsonResponse
    {
        $id = $r->cookie('cart_id');
        $cart = $id ? Cart::active()->with('items.product')->find($id) : null;

        if (!$cart) {
            $cart = Cart::create(['user_id' => auth()->id()]);
        }

        return $this->cartResponse($cart)
            ->cookie('cart_id', $cart->id, 60 * 24 * 30, '/', null, false, false, 'Lax');
    }

    public function show(string $id): JsonResponse
    {
        $cart = Cart::with('items.product')->findOrFail($id);
        return $this->cartResponse($cart);
    }

    public function addItem(Request $r, string $id): JsonResponse
    {
        $data = $r->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $qty = (int)($data['qty'] ?? 1);

        $cart = Cart::active()->findOrFail($id);

        return DB::transaction(function () use ($cart, $data, $qty) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            if ($qty > (int)$product->stock) {
                return response()->json(['message' => 'Not enough stock'], 422);
            }

            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $newQty = min($item->qty + $qty, (int)$product->stock);
                $item->update([
                    'qty' => $newQty,
                    'price' => $product->price,
                ]);
            } else {
                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $product->price,
                ]);
            }

            $cart->load('items.product');
            return $this->cartResponse($cart);
        });
    }

    public function updateItem(Request $r, string $id, CartItem $item): JsonResponse
    {
        $data = $r->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        if ($item->cart_id !== $id) {
            abort(404);
        }

        return DB::transaction(function () use ($data, $item) {
            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            $qty = min((int)$data['qty'], max(0, (int)$product->stock));
            if ($qty === 0) {
                $cartId = $item->cart_id;
                $item->delete();
                $cart = Cart::with('items.product')->findOrFail($cartId);
                return $this->cartResponse($cart);
            }

            $item->update([
                'qty' => $qty,
                'price' => $product->price,
            ]);

            $cart = Cart::with('items.product')->findOrFail($item->cart_id);
            return $this->cartResponse($cart);
        });
    }

    public function removeItem(string $id, CartItem $item): JsonResponse
    {
        if ($item->cart_id !== $id) {
            abort(404);
        }

        $item->delete();
        $cart = Cart::with('items.product')->findOrFail($id);
        return $this->cartResponse($cart);
    }

    private function cartResponse(Cart $cart): JsonResponse
    {
        $items = $cart->items->map(function (CartItem $i) {
            return [
                'id'         => $i->id,
                'product_id' => $i->product_id,
                'name'       => $i->product?->name,
                'slug'       => $i->product?->slug,
                'image'      => $i->product?->preview_url,
                'price'      => (float)$i->price,
                'qty'        => (int)$i->qty,
                'line_total' => (float)$i->price * (int)$i->qty,
            ];
        })->values();

        $total = $items->sum('line_total');

        return response()->json([
            'id'     => $cart->id,
            'status' => $cart->status,
            'items'  => $items,
            'total'  => $total,
        ]);
    }
}
