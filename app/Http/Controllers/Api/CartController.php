<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
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
}
