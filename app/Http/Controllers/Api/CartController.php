<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class CartController extends Controller
{
    public function store(Request $r): JsonResponse
    {
        $cart = Cart::query()->create(['user_id' => auth()->id()]);
        return response()
            ->json($cart)
            ->cookie(
                'cart_id',
                $cart->id,
                60*24*30, // 30 днів
                '/', null, false, false, // path, domain, secure, httpOnly
                'Lax'
            );
    }

    public function show(string $id): JsonResponse
    {
        $cart = Cart::with(['items.product:id,name,slug,price'])
            ->where('status','active')
            ->findOrFail($id);

        return response()->json($cart);
    }
    public function getOrCreate(Request $r): JsonResponse
    {
        $id = $r->cookie('cart_id');
        $cart = $id ? Cart::find($id) : null;
        if (!$cart) $cart = Cart::create(['user_id' => auth()->id()]);
        return response()
            ->json($cart->load('items.product'))
            ->cookie('cart_id', $cart->id, 60*24*30, '/', null, false, false, 'Lax');
    }

    public function updateItem(Request $r, Cart $cart, CartItem $item): JsonResponse
    {
        $data = $r->validate(['qty' => 'required|integer|min:0']);
        if ($item->cart_id !== $cart->id) abort(404);

        if ($data['qty'] === 0) {
            $item->delete();
            return response()->json(['ok'=>true]);
        }

        $product = $item->product()->lockForUpdate()->first();
        if (!$product) abort(422, 'Product not found');
        if ($data['qty'] > $product->stock) abort(422, 'Insufficient stock');

        $item->update(['qty' => $data['qty']]);
        return response()->json($cart->fresh()->load('items.product'));
    }
}
