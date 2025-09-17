<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->with('product')
            ->orderByDesc('wishlists.created_at')
            ->get()
            ->map(fn (Wishlist $wishlist) => $this->presentProduct($wishlist->product))
            ->filter()
            ->values();

        return response()->json($items);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        Wishlist::query()->upsert([
            [
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['user_id', 'product_id'], ['updated_at']);

        return response()->json($this->presentProduct($product));
    }

    public function destroy(Request $request, Product $product): Response
    {
        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->noContent();
    }

    private function presentProduct(?Product $product): ?array
    {
        if (!$product) {
            return null;
        }

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => $product->price,
            'preview_url' => $product->preview_url,
        ];
    }
}
