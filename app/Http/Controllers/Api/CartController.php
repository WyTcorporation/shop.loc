<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $r)
    {
        $cart = Cart::query()->create(['user_id' => auth()->id()]);
        return response()->json($cart, 201);
    }

    public function show(string $id)
    {
        $cart = Cart::with(['items.product:id,name,slug,price'])
            ->where('status','active')
            ->findOrFail($id);

        return response()->json($cart);
    }
}
