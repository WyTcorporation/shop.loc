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
        $locale = app()->getLocale() ?: config('app.locale');
        $fallbackLocale = config('app.fallback_locale') ?: $locale;
        $localizedNameSql = Product::localizedNameSql($locale, $fallbackLocale);
        $driver = Product::query()->getModel()->getConnection()->getDriverName();

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
                ->select(['id', 'name', 'name_translations', 'slug', 'price', 'price_cents'])
                ->selectRaw($localizedNameSql . ' as localized_name')
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->where(function ($qb) use ($localizedNameSql, $prefix, $wordMatch, $escaped, $driver) {
                    $qb->where(function ($qb) use ($localizedNameSql, $prefix, $wordMatch) {
                        $qb->whereRaw("{$localizedNameSql} LIKE ?", [$prefix])
                            ->orWhereRaw("{$localizedNameSql} LIKE ?", [$wordMatch]);
                    })
                        ->orWhere('sku', 'like', $prefix)
                        ->orWhere(function ($qb) use ($driver, $escaped) {
                            if ($driver === 'sqlite') {
                                $qb->whereRaw("EXISTS (SELECT 1 FROM json_each(name_translations) WHERE value LIKE ?)", ['%' . $escaped . '%']);
                            } elseif ($driver === 'pgsql') {
                                $qb->whereRaw(
                                    "EXISTS (SELECT 1 FROM jsonb_each_text(COALESCE(name_translations::jsonb, '{}'::jsonb)) WHERE value ILIKE ?)",
                                    ['%' . $escaped . '%']
                                );
                            } else {
                                $qb->whereRaw("JSON_SEARCH(name_translations, 'one', ?, NULL, '$.*') IS NOT NULL", ['%' . $escaped . '%']);
                            }
                        });
                })
                ->orderByRaw("CASE WHEN {$localizedNameSql} LIKE ? THEN 0 ELSE 1 END", [$prefix])
                ->orderByRaw("{$localizedNameSql} ASC")
                ->limit($limit)
                ->get();
        }

        $payload = $results->map(function (Product $product) use ($currency) {
            return [
                'id' => $product->id,
                'name' => $product->localized_name,
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
