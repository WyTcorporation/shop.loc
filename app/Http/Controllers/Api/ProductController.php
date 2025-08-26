<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products?search=&page=&per_page=
    public function index(Request $r)
    {
        $perPage = min(max((int) $r->integer('per_page', 12), 1), 100);
        $search  = trim((string) $r->query('search', ''));

        $qb = Product::query()->where('is_active', true);

        if ($search !== '') {
            // простий пошук через like; Scout/Meili додамо пізніше
            $qb->where('name', 'like', "%{$search}%");
        }

        $products = $qb->orderByDesc('id')->paginate($perPage);

        return response()->json($products);
    }

    // GET /api/products/{slug}
    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('images', 'category')
            ->firstOrFail();

        return response()->json($product);
    }
}
