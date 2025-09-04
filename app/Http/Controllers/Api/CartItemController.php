<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Cart, CartItem, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    public function store(Request $r, Cart $cart)
    {
        $data = $r->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty'        => ['required','integer','min:1'],
        ]);

        return DB::transaction(function () use ($cart, $data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            if ($product->stock < $data['qty']) {
                return response()->json(['message' => 'Not enough stock'], 422);
            }

            /** @var CartItem $item */
            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
            $item->qty   = ($item->exists ? $item->qty : 0) + $data['qty'];
            $item->price = $product->price; // snapshot
            $item->save();

            return $cart->load('items.product:id,name,slug,price');
        });
    }

    public function destroy(Cart $cart, int $itemId)
    {
        $cart->items()->whereKey($itemId)->delete();
        return response()->json($cart->load('items.product:id,name,slug,price'));
    }
}
