<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('q') ?? $request->query('query', '')));

        if ($query === '') {
            return response()->json([
                'data' => [],
                'query' => $query,
            ]);
        }

        $limit = (int) $request->integer('limit', 8);
        if ($limit <= 0) {
            $limit = 8;
        }
        $limit = min($limit, 20);

        $currency = config('shop.currency.base', 'EUR');

        try {
            $results = Product::search($query)
                ->where('is_active', true)
                ->take($limit)
                ->get();
        } catch (\Throwable $e) {
            $escaped = addcslashes($query, '\\%_');
            $prefix = $escaped . '%';
            $wordMatch = '% ' . $escaped . '%';

            $results = Product::query()
                ->select(['id', 'name', 'slug', 'price', 'price_cents'])
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->where(function ($qb) use ($prefix, $wordMatch) {
                    $qb->where('name', 'like', $prefix)
                        ->orWhere('name', 'like', $wordMatch)
                        ->orWhere('sku', 'like', $prefix);
                })
                ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$prefix])
                ->orderBy('name')
                ->limit($limit)
                ->get();
        }

        $payload = $results->map(function (Product $product) use ($currency) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'preview_url' => $product->preview_url,
                'price' => $product->price !== null ? round((float) $product->price, 2) : null,
                'currency' => $currency,
            ];
        })->values();

        return response()->json([
            'data' => $payload,
            'query' => $query,
            'driver' => config('scout.driver'),
        ]);
    }
}
