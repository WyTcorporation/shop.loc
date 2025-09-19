<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Meilisearch\Client as Meili;
use Meilisearch\Search\SearchResult;


class ProductController extends Controller
{
    public function __construct(private CurrencyConverter $converter)
    {
    }

    public function index(Request $r): JsonResponse
    {
        $currency = $this->resolveCurrency($r);

        $perPage    = max(1, min((int)$r->integer('per_page', 12), 100));
        $page       = max(1, (int)$r->integer('page', 1));
        $search     = trim((string) $r->query('search', ''));
        $categoryId = $r->integer('category_id');
        $sort       = (string) $r->query('sort', 'new'); // new|price_asc|price_desc
        $withFacets = $r->boolean('with_facets');

        $minPrice = $r->has('min_price') ? (float)$r->query('min_price') : null;
        $maxPrice = $r->has('max_price') ? (float)$r->query('max_price') : null;

        if ($minPrice !== null) {
            $minPrice = $this->converter->convertToBase($minPrice, $currency);
        }

        if ($maxPrice !== null) {
            $maxPrice = $this->converter->convertToBase($maxPrice, $currency);
        }


        $colors = $this->extractFilterValues($r, 'color');
        $sizes = $this->extractFilterValues($r, 'size');

        $locale = app()->getLocale() ?: config('app.locale');
        $fallbackLocale = config('app.fallback_locale') ?: $locale;
        $localizedNameSql = Product::localizedNameSql($locale, $fallbackLocale);
        $driver = Product::query()->getModel()->getConnection()->getDriverName();

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
                    'facets'       => (object) $this->transformFacetDistribution([]),
                ]);
            }
            $qb->whereIn('id', $ids->all());
        } else {
            // ===== DB fallback =====
            if ($search !== '' && config('scout.driver') !== 'meilisearch') {
                $escaped = addcslashes($search, '\\%_');
                $like = '%'.$escaped.'%';

                $qb->where(function (Builder $query) use ($localizedNameSql, $like, $driver, $escaped) {
                    $query->whereRaw("{$localizedNameSql} LIKE ?", [$like])
                        ->orWhere('sku', 'like', $like)
                        ->orWhere(function (Builder $query) use ($driver, $escaped) {
                            $jsonLike = '%'.$escaped.'%';

                            if ($driver === 'sqlite') {
                                $query->whereRaw(
                                    "EXISTS (SELECT 1 FROM json_each(COALESCE(name_translations, '{}')) WHERE value LIKE ?)",
                                    [$jsonLike]
                                );
                            } elseif ($driver === 'pgsql') {
                                $query->whereRaw(
                                    "EXISTS (SELECT 1 FROM jsonb_each_text(COALESCE(name_translations::jsonb, '{}'::jsonb)) WHERE value ILIKE ?)",
                                    [$jsonLike]
                                );
                            } else {
                                $query->whereRaw(
                                    "JSON_SEARCH(COALESCE(name_translations, JSON_OBJECT()), 'one', ?, NULL, '$.*') IS NOT NULL",
                                    [$jsonLike]
                                );
                            }
                        });
                });
            }
            if ($categoryId) {
                $qb->where('category_id', $categoryId);
            }
            if ($colors) {
                $qb->where(function ($query) use ($colors) {
                    $driver = DB::connection()->getDriverName();
                    foreach ($colors as $index => $color) {
                        if ($driver === 'sqlite') {
                            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                            $query->{$method}(
                                "EXISTS (SELECT 1 FROM json_each(COALESCE(attributes, '[]')) AS attr WHERE json_extract(attr.value, '$.key') = ? AND json_extract(attr.value, '$.value') = ?)",
                                ['color', $color]
                            );
                        } else {
                            $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                            $query->{$method}('attributes', [['key' => 'color', 'value' => $color]]);
                        }
                    }
                });
            }
            if ($sizes) {
                $qb->where(function ($query) use ($sizes) {
                    $driver = DB::connection()->getDriverName();
                    foreach ($sizes as $index => $size) {
                        if ($driver === 'sqlite') {
                            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                            $query->{$method}(
                                "EXISTS (SELECT 1 FROM json_each(COALESCE(attributes, '[]')) AS attr WHERE json_extract(attr.value, '$.key') = ? AND json_extract(attr.value, '$.value') = ?)",
                                ['size', $size]
                            );
                        } else {
                            $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                            $query->{$method}('attributes', [['key' => 'size', 'value' => $size]]);
                        }
                    }
                });
            }
            if ($minPrice !== null) $qb->where('price', '>=', (float)$minPrice);
            if ($maxPrice !== null) $qb->where('price', '<=', (float)$maxPrice);
        }

        match ($sort) {
            'price_asc'  => $qb->orderBy('price'),
            'price_desc' => $qb->orderByDesc('price'),
            default      => $qb->orderByDesc('id'),
        };

        $facetsBase = null;
        if ($withFacets && config('scout.driver') !== 'meilisearch') {
            $facetsBase = clone $qb;
        }

        $pag = $qb->paginate($perPage, ['*'], 'page', $page);

        $pag->setCollection(
            $pag->getCollection()->map(fn (Product $product) => $this->transformProduct($product, $currency))
        );

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

                    $facetsPayload = (object) $this->transformFacetDistribution($facetDist);
                } catch (\Throwable $e) {
                    $facetsPayload = (object) $this->transformFacetDistribution([]);
                }
            } else {
                $facetsPayload = (object) $this->sqlFacets($facetsBase ?? clone $qb);
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
        $categoryCounts = (clone $base)
            ->selectRaw('category_id, COUNT(*) as c')
            ->groupBy('category_id')
            ->pluck('c', 'category_id')
            ->toArray();

        $filtered = (clone $base)->select('id');

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $colorCounts = DB::query()
                ->fromSub($filtered, 'filtered_products')
                ->join('products', 'products.id', '=', 'filtered_products.id')
                ->join(DB::raw("json_each(COALESCE(products.attributes, '[]')) AS attr"), DB::raw('1'), '=', DB::raw('1'))
                ->whereRaw("json_extract(attr.value, '$.key') = ?", ['color'])
                ->selectRaw("json_extract(attr.value, '$.value') as value, COUNT(*) as c")
                ->groupBy('value')
                ->pluck('c', 'value')
                ->toArray();

            $sizeCounts = DB::query()
                ->fromSub($filtered, 'filtered_products_sizes')
                ->join('products', 'products.id', '=', 'filtered_products_sizes.id')
                ->join(DB::raw("json_each(COALESCE(products.attributes, '[]')) AS attr"), DB::raw('1'), '=', DB::raw('1'))
                ->whereRaw("json_extract(attr.value, '$.key') = ?", ['size'])
                ->selectRaw("json_extract(attr.value, '$.value') as value, COUNT(*) as c")
                ->groupBy('value')
                ->pluck('c', 'value')
                ->toArray();
        } else {
            $colorCounts = DB::query()
                ->fromSub($filtered, 'filtered_products')
                ->join('products', 'products.id', '=', 'filtered_products.id')
                ->joinRaw('JOIN LATERAL jsonb_array_elements(products.attributes) AS attr ON true')
                ->whereRaw("attr->>'key' = 'color'")
                ->selectRaw("attr->>'value' as value, COUNT(*) as c")
                ->groupBy('value')
                ->pluck('c', 'value')
                ->toArray();

            $sizeCounts = DB::query()
                ->fromSub($filtered, 'filtered_products_sizes')
                ->join('products', 'products.id', '=', 'filtered_products_sizes.id')
                ->joinRaw('JOIN LATERAL jsonb_array_elements(products.attributes) AS attr ON true')
                ->whereRaw("attr->>'key' = 'size'")
                ->selectRaw("attr->>'value' as value, COUNT(*) as c")
                ->groupBy('value')
                ->pluck('c', 'value')
                ->toArray();
        }

        return [
            'category_id' => (object) $categoryCounts,
            'attrs.color' => $this->attributeFacetPayload($colorCounts, 'color'),
            'attrs.size' => $this->attributeFacetPayload($sizeCounts, 'size'),
        ];
    }

    private function transformFacetDistribution(array $facetDist): array
    {
        $categoryCounts = $this->normalizeCategoryFacet($facetDist['category_id'] ?? []);
        $colorCounts = $facetDist['attrs.color'] ?? [];
        $sizeCounts = $facetDist['attrs.size'] ?? [];

        return [
            'category_id' => (object) $categoryCounts,
            'attrs.color' => $this->attributeFacetPayload($colorCounts, 'color'),
            'attrs.size' => $this->attributeFacetPayload($sizeCounts, 'size'),
        ];
    }

    private function attributeFacetPayload($rawCounts, string $attributeKey): object
    {
        $counts = $this->convertFacetCounts($rawCounts);

        if ($counts === []) {
            return (object) [];
        }

        $locale = app()->getLocale();
        $defaultLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);

        $translationsMap = $this->fetchAttributeTranslations($attributeKey, array_keys($counts));

        $formatted = [];

        foreach ($counts as $value => $count) {
            $valueString = is_string($value) ? $value : (string) $value;
            if ($valueString === '' || strtolower($valueString) === 'null') {
                continue;
            }

            $translations = $translationsMap[$valueString] ?? [];

            $label = $translations[$locale]
                ?? ($fallbackLocale && isset($translations[$fallbackLocale]) ? $translations[$fallbackLocale] : null)
                ?? ($translations[$defaultLocale] ?? $valueString);

            $formatted[$valueString] = [
                'value' => $valueString,
                'count' => (int) $count,
                'label' => $label,
                'translations' => $translations,
            ];
        }

        return (object) $formatted;
    }

    private function fetchAttributeTranslations(string $attributeKey, array $values): array
    {
        $values = array_values(array_unique(array_map(fn ($value) => (string) $value, $values)));

        if ($values === []) {
            return [];
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::query()
                ->selectRaw("json_extract(attr.value, '$.value') as value, json_extract(attr.value, '$.translations') as translations")
                ->from('products')
                ->join(DB::raw("json_each(COALESCE(products.attributes, '[]')) AS attr"), DB::raw('1'), '=', DB::raw('1'))
                ->whereRaw("json_extract(attr.value, '$.key') = ?", [$attributeKey])
                ->whereIn(DB::raw("json_extract(attr.value, '$.value')"), $values)
                ->distinct()
                ->get();
        } else {
            $rows = DB::query()
                ->selectRaw("attr->>'value' as value, attr->'translations' as translations")
                ->fromRaw('products, jsonb_array_elements(products.attributes) as attr')
                ->whereRaw("attr->>'key' = ?", [$attributeKey])
                ->whereIn(DB::raw("attr->>'value'"), $values)
                ->distinct()
                ->get();
        }

        $map = [];

        foreach ($rows as $row) {
            $raw = $row->translations;
            if (is_string($raw)) {
                $translations = json_decode($raw, true);
            } else {
                $translations = json_decode(json_encode($raw), true);
            }

            if (!is_array($translations)) {
                $translations = [];
            }

            $map[$row->value] = $translations;
        }

        return $map;
    }

    private function normalizeCategoryFacet($counts): array
    {
        $array = $this->convertFacetCounts($counts);
        $normalized = [];

        foreach ($array as $key => $count) {
            if ($key === null || $key === '' || strtolower((string) $key) === 'null') {
                continue;
            }

            $normalized[$this->normalizeFacetKey($key)] = (int) $count;
        }

        return $normalized;
    }

    private function convertFacetCounts($counts): array
    {
        if ($counts instanceof Collection) {
            return $counts->all();
        }

        if ($counts instanceof \stdClass) {
            return (array) $counts;
        }

        if ($counts instanceof \Traversable) {
            return iterator_to_array($counts);
        }

        if (is_array($counts)) {
            return $counts;
        }

        return [];
    }

    private function normalizeFacetKey($key): int|string
    {
        if (is_numeric($key)) {
            return (int) $key;
        }

        return (string) $key;
    }


    public function show(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'images' => fn($q) => $q->orderBy('sort'),
                'category',
                'vendor',
            ])
            ->firstOrFail();

        $currency = $this->resolveCurrency($request);

        return response()->json($this->transformProduct($product, $currency));
    }

    public function sellerProducts(Request $request, int $vendorId): JsonResponse
    {
        $vendor = Vendor::query()->findOrFail($vendorId);
        $currency = $this->resolveCurrency($request);

        $perPage = max(1, min((int) $request->integer('per_page', 12), 100));
        $page = max(1, (int) $request->integer('page', 1));

        $paginated = $vendor->products()
            ->where('is_active', true)
            ->with(['images' => fn ($query) => $query->orderBy('sort'), 'category', 'vendor'])
            ->paginate($perPage, ['*'], 'page', $page);

        $paginated->setCollection(
            $paginated->getCollection()->map(fn (Product $product) => $this->transformProduct($product, $currency))
        );

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'slug' => $vendor->slug,
                'contact_email' => $vendor->contact_email,
                'contact_phone' => $vendor->contact_phone,
                'description' => $vendor->description,
            ],
        ]);
    }

    private function transformProduct(Product $product, string $currency): array
    {
        $data = $product->toArray();
        if ($product->localized_name !== null) {
            $data['name'] = $product->localized_name;
        }
        $data['description'] = $product->description;

        $baseCurrency = $this->converter->getBaseCurrency();
        $basePriceCents = $product->price_cents ?? (int) round((float) $product->price * 100);
        $convertedPriceCents = $this->converter->convertBaseCents($basePriceCents, $currency);

        $data['base_currency'] = $baseCurrency;
        $data['currency'] = $currency;
        $data['base_price_cents'] = $basePriceCents;
        $data['price_cents'] = $convertedPriceCents;
        $data['price'] = round($convertedPriceCents / 100, 2);

        if (array_key_exists('price_old', $data)) {
            $data['price_old'] = $data['price_old'] !== null
                ? $this->converter->convertFromBase((float) $product->price_old, $currency)
                : null;
        }

        $locale = app()->getLocale();
        $defaultLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);

        $attributes = [];
        foreach ($product->attributeDefinitions() as $attribute) {
            $translations = $attribute['translations'];
            $label = $translations[$locale]
                ?? ($fallbackLocale && isset($translations[$fallbackLocale]) ? $translations[$fallbackLocale] : null)
                ?? ($translations[$defaultLocale] ?? $attribute['value']);

            $attributes[] = [
                'key' => $attribute['key'],
                'value' => $attribute['value'],
                'label' => $label,
                'translations' => $translations,
            ];
        }

        $data['attributes'] = $attributes;
        $data['attribute_values'] = collect($attributes)->mapWithKeys(fn ($attr) => [$attr['key'] => $attr['value']])->all();

        $data['vendor'] = $product->relationLoaded('vendor') && $product->vendor
            ? [
                'id' => $product->vendor->id,
                'name' => $product->vendor->name,
                'slug' => $product->vendor->slug,
                'contact_email' => $product->vendor->contact_email,
                'contact_phone' => $product->vendor->contact_phone,
            ]
            : null;

        return $data;
    }

    private function resolveCurrency(Request $request): string
    {
        $routeCurrency = $request->route('currency');
        $queryCurrency = $request->query('currency');

        return $this->converter->normalizeCurrency($routeCurrency ?? $queryCurrency);
    }

    private function extractFilterValues(Request $request, string $key): array
    {
        $values = [];

        $direct = $request->query($key);
        if ($direct !== null) {
            $values = array_merge($values, $this->normalizeFilterInput($direct));
        }

        $nested = $request->query('filter');
        if (is_array($nested) && array_key_exists($key, $nested)) {
            $values = array_merge($values, $this->normalizeFilterInput($nested[$key]));
        }

        $values = array_map(fn ($value) => trim((string) $value), $values);
        $values = array_values(array_unique(array_filter($values, fn ($value) => $value !== '')));

        return $values;
    }

    private function normalizeFilterInput(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return explode(',', (string) $input);
    }

    public function facets(Request $r): JsonResponse
    {
        if (config('scout.driver') !== 'meilisearch') {
            $payload = Cache::remember('products:facets:db', now()->addMinutes(10), function () {
                $base = Product::query()->where('is_active', true);

                return [
                    'facets' => (object) $this->sqlFacets(clone $base),
                    'nbHits' => $base->count(),
                    'driver' => 'db',
                    'error' => null,
                ];
            });

            return response()->json($payload);
        }

        $search = (string)$r->query('search', '');
        $categoryId = $r->integer('category_id');
        $colors = $this->extractFilterValues($r, 'color');
        $sizes = $this->extractFilterValues($r, 'size');

        $cacheKey = $this->facetsCacheKey($search, $categoryId, $colors, $sizes);

        // фільтр Meili
        $parts = ['is_active = true'];
        if ($categoryId) {
            $parts[] = "category_id = {$categoryId}";
        }
        $q = fn($v) => "'" . str_replace("'", "\\'", (string) $v) . "'";
        if ($colors) {
            $parts[] = '(' . implode(' OR ', array_map(fn($c) => "attrs.color = " . $q($c), $colors)) . ')';
        }
        if ($sizes) {
            $parts[] = '(' . implode(' OR ', array_map(fn($s) => "attrs.size = " . $q($s), $sizes)) . ')';
        }
        $filter = implode(' AND ', $parts);

        try {
            $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search, $filter) {
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

                $facets = $this->transformFacetDistribution($facetDist);

                return [
                    'facets' => (object) $facets,
                    'nbHits' => (int) $hits,
                    'driver' => 'meilisearch',
                    'error' => null,
                ];
            });

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'facets' => (object) $this->transformFacetDistribution([]),
                'nbHits' => 0,
                'driver' => 'meilisearch-error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function facetsCacheKey(string $search, ?int $categoryId, array $colors, array $sizes): string
    {
        sort($colors);
        sort($sizes);

        $version = (int) Cache::get(Product::FACETS_CACHE_VERSION_KEY, 1);

        return 'products:facets:' . $version . ':' . md5(json_encode([
            'search' => $search,
            'category' => $categoryId,
            'colors' => $colors,
            'sizes' => $sizes,
        ]));
    }
}
