<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(Request $r): JsonResponse
    {

        $perPage = (int) $r->integer('per_page', 12);
        $perPage = max(1, min($perPage, 100));
        $search     = trim((string) $r->query('search', ''));
        $categoryId = $r->integer('category');
        $sort       = (string) $r->query('sort', 'new'); // new|price_asc|price_desc

        $qb = Product::query()->where('is_active', true);

        if ($search !== '' && config('scout.driver') === 'meilisearch') {
            $ids = Product::search($search)->take(1000)->keys();
            $qb->whereIn('id', $ids->all());
        } elseif ($search !== '') {
            $qb->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $qb->where('category_id', $categoryId);
        }

        match ($sort) {
            'price_asc'  => $qb->orderBy('price'),
            'price_desc' => $qb->orderByDesc('price'),
            default      => $qb->orderByDesc('id'), // newest
        };

        return response()->json($qb->paginate($perPage));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['images' => fn($q) => $q->orderBy('sort'),'category'])
            ->firstOrFail();

        return response()->json($product);
    }
}
