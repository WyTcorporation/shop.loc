<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Client as Meili;
use Meilisearch\Search\SearchResult;


class ProductController extends Controller
{


    public function index(Request $r): JsonResponse
    {
        $perPage    = max(1, min((int)$r->integer('per_page', 12), 100));
        $page       = max(1, (int)$r->integer('page', 1));
        $search     = trim((string) $r->query('search', ''));
        $categoryId = $r->integer('category_id');
        $sort       = (string) $r->query('sort', 'new'); // new|price_asc|price_desc
        $withFacets = $r->boolean('with_facets');

        $minPrice = $r->has('min_price') ? (float)$r->query('min_price') : null;
        $maxPrice = $r->has('max_price') ? (float)$r->query('max_price') : null;


        $colors = $r->has('color')
            ? (is_array($r->color) ? array_filter($r->color) : array_filter(explode(',', (string)$r->color)))
            : [];
        $sizes  = $r->has('size')
            ? (is_array($r->size) ? array_filter($r->size) : array_filter(explode(',', (string)$r->size)))
            : [];

        $qb = Product::query()->where('is_active', true);

        if (config('scout.driver') === 'meilisearch' && ($search !== '' || $categoryId || $colors || $sizes || $minPrice !== null || $maxPrice !== null)) {
            $parts = ['is_active = true'];
            if ($categoryId) $parts[] = "category_id = {$categoryId}";
            $q = fn($v) => "'".str_replace("'", "\\'", (string)$v)."'";
            if ($colors) $parts[] = '(' . implode(' OR ', array_map(fn($c) => "attrs.color = ".$q($c), $colors)) . ')';
            if ($sizes)  $parts[] = '(' . implode(' OR ', array_map(fn($s) => "attrs.size = ".$q($s),  $sizes))  . ')';
            if ($minPrice !== null) $parts[] = "price >= ".(float)$minPrice;
            if ($maxPrice !== null) $parts[] = "price <= ".(float)$maxPrice;
            $filter = implode(' AND ', $parts);

            /** @var Meili $meili */
            $meili = app(Meili::class);
            $index = $meili->index((new Product)->searchableAs());

            $result = $index->search($search, [
                'filter' => $filter,
                'limit'  => 2000,
                'attributesToRetrieve' => ['id'],
            ]);

            $hits = $result instanceof SearchResult
                ? $result->getHits()
                : ($result['hits'] ?? []);
            $ids = collect($hits)->pluck('id')->filter()->values();

            if ($ids->isEmpty()) {
                return response()->json([
                    'data'         => [],
                    'current_page' => $page,
                    'last_page'    => 1,
                    'per_page'     => $perPage,
                    'total'        => 0,
                    'facets'       => (object) ['category_id'=>(object)[], 'attrs.color'=>(object)[], 'attrs.size'=>(object)[]],
                ]);
            }
            $qb->whereIn('id', $ids->all());
        } else {
            // ===== DB fallback =====
            if ($search !== '' && config('scout.driver') !== 'meilisearch') {
                $qb->where('name', 'like', "%{$search}%");
            }
            if ($categoryId) {
                $qb->where('category_id', $categoryId);
            }
            if ($colors) {
                $qb->whereIn(DB::raw("(attributes->>'color')"), $colors);
            }
            if ($sizes) {
                $qb->whereIn(DB::raw("(attributes->>'size')"), $sizes);
            }
            if ($minPrice !== null) $qb->where('price', '>=', (float)$minPrice);
            if ($maxPrice !== null) $qb->where('price', '<=', (float)$maxPrice);
        }

        match ($sort) {
            'price_asc'  => $qb->orderBy('price'),
            'price_desc' => $qb->orderByDesc('price'),
            default      => $qb->orderByDesc('id'),
        };

        $pag = $qb->paginate($perPage, ['*'], 'page', $page);

        // ===== Facets (optional) =====
        $facetsPayload = (object)[];
        if ($withFacets) {
            if (config('scout.driver') === 'meilisearch') {
                try {
                    /** @var Meili $meili */
                    $meili = app(Meili::class);
                    $index = $meili->index((new Product)->searchableAs());

                    $parts = ['is_active = true'];
                    if ($categoryId) $parts[] = "category_id = {$categoryId}";
                    $q = fn($v) => "'".str_replace("'", "\\'", (string)$v)."'";
                    if ($colors) $parts[] = '(' . implode(' OR ', array_map(fn($c) => "attrs.color = ".$q($c), $colors)) . ')';
                    if ($sizes)  $parts[] = '(' . implode(' OR ', array_map(fn($s) => "attrs.size = ".$q($s),  $sizes))  . ')';
                    if ($minPrice !== null) $parts[] = "price >= ".(float)$minPrice;
                    if ($maxPrice !== null) $parts[] = "price <= ".(float)$maxPrice;
                    $filter = implode(' AND ', $parts);

                    $res = $index->search($search, [
                        'facets' => ['category_id', 'attrs.color', 'attrs.size'],
                        'filter' => $filter,
                        'limit'  => 0,
                    ]);

                    $facetDist = $res instanceof SearchResult
                        ? ($res->getFacetDistribution() ?? [])
                        : ($res['facetDistribution'] ?? []);

                    $facetsPayload = (object)$facetDist;
                } catch (\Throwable $e) {
                    $facetsPayload = (object)['category_id'=>(object)[], 'attrs.color'=>(object)[], 'attrs.size'=>(object)[]];
                }
            } else {
                $facetsPayload = (object)[
                    'category_id' => (object) Product::query()
                        ->where('is_active', true)
                        ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
                        ->select('category_id', DB::raw('count(*) as cnt'))
                        ->groupBy('category_id')
                        ->pluck('cnt','category_id')
                        ->toArray(),
                    'attrs.color' => (object)[],
                    'attrs.size'  => (object)[],
                ];
            }
        }

        return response()->json([
            'data'         => $pag->items(),
            'current_page' => $pag->currentPage(),
            'last_page'    => $pag->lastPage(),
            'per_page'     => $pag->perPage(),
            'total'        => $pag->total(),
            'facets'       => $facetsPayload,
        ]);
    }



    private function sqlFacets(Builder $base): array
    {
        // Категорії
        $cats = (clone $base)
            ->selectRaw('category_id, COUNT(*) as c')
            ->groupBy('category_id')
            ->pluck('c', 'category_id');

        // Колір (JSONB → text)
        $colors = (clone $base)
            ->selectRaw("COALESCE(attributes->>'color','') as color, COUNT(*) as c")
            ->groupBy('color')
            ->pluck('c', 'color');

        // Розмір
        $sizes = (clone $base)
            ->selectRaw("COALESCE(attributes->>'size','') as size, COUNT(*) as c")
            ->groupBy('size')
            ->pluck('c', 'size');

        return [
            'category_id' => (object)$cats,
            'attrs.color' => (object)$colors,
            'attrs.size' => (object)$sizes,
        ];
    }


    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['images' => fn($q) => $q->orderBy('sort'), 'category'])
            ->firstOrFail();

        return response()->json($product);
    }

    public function facets(Request $r): JsonResponse
    {
        if (config('scout.driver') !== 'meilisearch') {
            return response()->json([
                'facets' => (object)['category_id' => (object)[], 'attrs.color' => (object)[], 'attrs.size' => (object)[]],
                'nbHits' => 0,
                'driver' => 'db',
                'error' => null,
            ]);
        }

        $search = (string)$r->query('search', '');
        $categoryId = $r->integer('category_id');
        $colors = array_values(array_filter((array)$r->query('color', []), fn($v) => $v !== '' && $v !== null));
        $sizes = array_values(array_filter((array)$r->query('size', []), fn($v) => $v !== '' && $v !== null));

        // фільтр Meili
        $parts = ['is_active = true'];
        if ($categoryId) $parts[] = "category_id = {$categoryId}";
        $q = fn($v) => "'" . str_replace("'", "\\'", (string)$v) . "'";
        if ($colors) $parts[] = '(' . implode(' OR ', array_map(fn($c) => "attrs.color = " . $q($c), $colors)) . ')';
        if ($sizes) $parts[] = '(' . implode(' OR ', array_map(fn($s) => "attrs.size = " . $q($s), $sizes)) . ')';
        $filter = implode(' AND ', $parts);

        try {
            /** @var Meili $meili */
            $meili = app(Meili::class);
            $index = $meili->index((new Product)->searchableAs());

            // ВАЖЛИВО: у v1 параметр називається "facets"; у відповіді — поле/метод "facetDistribution".
            $result = $index->search($search, [
                'facets' => ['category_id', 'attrs.color', 'attrs.size'],
                'filter' => $filter,
                'limit' => 0, // потрібні лише підрахунки
            ]);

            // Сумісність з різними версіями PHP-SDK: масив vs SearchResult
            $facetDist = [];
            $hits = 0;

            if (is_array($result)) {
                $facetDist = $result['facetDistribution'] ?? [];
                $hits = $result['estimatedTotalHits'] ?? ($result['nbHits'] ?? 0);
            } elseif ($result instanceof \Meilisearch\Search\SearchResult) {
                $facetDist = $result->getFacetDistribution() ?? [];
                $hits = method_exists($result, 'getEstimatedTotalHits')
                    ? ($result->getEstimatedTotalHits() ?? 0)
                    : ($result->getNbHits() ?? 0);
            }

            return response()->json([
                'facets' => (object)$facetDist,
                'nbHits' => (int)$hits,
                'driver' => 'meilisearch',
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'facets' => (object)['category_id' => (object)[], 'attrs.color' => (object)[], 'attrs.size' => (object)[]],
                'nbHits' => 0,
                'driver' => 'meilisearch-error',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
