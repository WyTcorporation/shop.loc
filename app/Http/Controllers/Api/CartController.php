<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
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

//    public function show(string $id): JsonResponse
//    {
//        $cart = Cart::with(['items.product:id,name,slug,price'])
//            ->where('status','active')
//            ->findOrFail($id);
//
//        return response()->json($cart);
//    }

    public function show(Cart $cart)
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()
            ->with(['items.product' => fn($q) => $q->select('id','name','price')])
            ->findOrFail($cart->id);

        $total = $cart->items->sum(fn ($i) => (float)$i->price * (int)$i->qty);

        return response()->json([
            'id' => $cart->id,
            'items' => $cart->items->map(fn (CartItem $i) => [
                'id'         => $i->id,
                'product_id' => $i->product_id,
                'name'       => $i->product?->name,
                'price'      => (float)$i->price,
                'qty'        => (int)$i->qty,
            ])->values(),
            'total' => $total,
        ]);
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


    public function updateItem(Request $request, Cart $cart, CartItem $item)
    {
        $data = $request->validate([
            'qty' => ['required','integer','min:0','max:100000'],
        ]);

        if ($item->cart_id !== $cart->id) {
            abort(404);
        }

        /** @var Product $product */
        $product = Product::findOrFail($item->product_id);

        // clamp по складу
        $qty = min((int)$data['qty'], max(0, (int)$product->stock));

        if ($qty === 0) {
            $item->delete();

            return response()->json([
                'ok' => true,
                'removed' => true,
            ]);
        }

        $item->update([
            'qty'   => $qty,
            'price' => $product->price,
        ]);

        return response()->json($cart->fresh('items'));

//        return response()->json([
//            'ok' => true,
//            'item' => [
//                'id'         => $item->id,
//                'product_id' => $item->product_id,
//                'qty'        => (int)$item->qty,
//                'price'      => (float)$item->price,
//            ],
//        ]);
    }
}
