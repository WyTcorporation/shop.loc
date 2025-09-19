<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;
    use HasTranslations {
        HasTranslations::initializeHasTranslations as protected initializeTranslationsTrait;
    }
    use Searchable;

    public const FACETS_CACHE_VERSION_KEY = 'products:facets:version';

    protected $fillable = [
        'name',
        'name_translations',
        'description',
        'description_translations',
        'slug',
        'sku',
        'category_id',
        'vendor_id',
        'attributes',
        'stock',
        'price',
        'price_cents',
        'price_old',
        'is_active',
        'reviews_count',
        'rating',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'price_cents' => 'integer',
        'price_old' => 'decimal:2',
        'reviews_count' => 'integer',
        'rating' => 'decimal:2',
        'name_translations' => 'array',
        'description_translations' => 'array',
    ];

    public function initializeHasTranslations(): void
    {
        $this->translatable = ['name', 'description'];
        $this->initializeTranslationsTrait();
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function (Product $product) {
            if ($product->isDirty('price')) {
                $price = max(0, (float) $product->price);
                $product->price_cents = (int) round($price * 100);
            } elseif ($product->isDirty('price_cents') && ! $product->isDirty('price')) {
                $product->price = round(((int) $product->price_cents) / 100, 2);
            }
        });

        $invalidate = fn () => static::bumpFacetsCacheVersion();

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    protected static function bumpFacetsCacheVersion(): void
    {
        $key = self::FACETS_CACHE_VERSION_KEY;
        $current = (int) Cache::get($key, 0);
        Cache::forever($key, $current + 1);
    }

    public static function localizedNameSql(?string $locale = null, ?string $fallbackLocale = null): string
    {
        $locale = $locale ?: app()->getLocale() ?: config('app.locale');
        $fallbackLocale = $fallbackLocale ?: config('app.fallback_locale') ?: $locale;
        $driver = static::databaseDriver();

        $segments = [];

        if ($locale) {
            $segments[] = static::jsonTranslationSelect($locale);
        }

        if ($fallbackLocale && $fallbackLocale !== $locale) {
            $segments[] = static::jsonTranslationSelect($fallbackLocale);
        }

        $segments[] = static::jsonFirstTranslationSelect($driver);
        $segments[] = 'name';

        $segments = array_values(array_unique(array_filter($segments)));

        return 'COALESCE(' . implode(', ', $segments) . ')';
    }

    public static function localizedNameSelect(string $alias = 'localized_name', ?string $locale = null, ?string $fallbackLocale = null)
    {
        $sql = static::localizedNameSql($locale, $fallbackLocale);

        return DB::raw("{$sql} as {$alias}");
    }

    public function getLocalizedNameAttribute(): ?string
    {
        $locale = app()->getLocale() ?: config('app.locale');
        $fallbackLocale = config('app.fallback_locale') ?: $locale;

        $translations = (array) ($this->name_translations ?? []);

        if ($locale && isset($translations[$locale]) && $translations[$locale] !== '') {
            return $translations[$locale];
        }

        if ($fallbackLocale && isset($translations[$fallbackLocale]) && $translations[$fallbackLocale] !== '') {
            return $translations[$fallbackLocale];
        }

        if ($translations !== []) {
            $first = reset($translations);

            if (is_string($first) && $first !== '') {
                return $first;
            }
        }

        $name = parent::getAttribute('name');

        return $name !== null ? (string) $name : null;
    }

    protected static function jsonTranslationSelect(string $locale): string
    {
        $path = static::jsonLocalePath($locale);

        if (static::databaseDriver() === 'sqlite') {
            return "json_extract(name_translations, '{$path}')";
        }

        return "JSON_UNQUOTE(JSON_EXTRACT(name_translations, '{$path}'))";
    }

    protected static function jsonLocalePath(string $locale): string
    {
        $normalized = str_replace('\\', '\\', $locale);
        $normalized = str_replace("'", "''", $normalized);

        return '$."' . $normalized . '"';
    }

    protected static function jsonFirstTranslationSelect(string $driver): string
    {
        if ($driver === 'sqlite') {
            return '(SELECT value FROM json_each(name_translations) LIMIT 1)';
        }

        $single = "'";
        $double = '"';

        return 'JSON_UNQUOTE(JSON_EXTRACT(name_translations, CONCAT('
            . $single . '$.' . $double . $single . ', '
            . 'JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name_translations), ' . $single . '$[0]' . $single . ')), '
            . $single . $double . $single
            . ')))';
    }

    protected static function databaseDriver(): string
    {
        return DB::connection((new static())->getConnectionName())->getDriverName();
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function adjustStock(int $delta, ?int $warehouseId = null): void
    {
        if ($delta === 0) {
            return;
        }

        $warehouseId ??= Warehouse::getDefault()->id;

        DB::transaction(function () use ($delta, $warehouseId) {
            $stock = $this->stockForUpdate($warehouseId);

            $newQty = $stock->qty + $delta;

            if ($newQty < $stock->reserved) {
                throw new \DomainException("Not enough stock for product {$this->id} at warehouse {$warehouseId}");
            }

            $stock->qty = $newQty;
            $stock->save();

            $this->syncAvailableStock();
        });
    }

    public function reserveStock(int $qty, ?int $warehouseId = null): void
    {
        if ($qty <= 0) {
            return;
        }

        $warehouseId ??= Warehouse::getDefault()->id;

        DB::transaction(function () use ($qty, $warehouseId) {
            $stock = $this->stockForUpdate($warehouseId);

            $available = $stock->qty - $stock->reserved;

            if ($available < $qty) {
                throw new \DomainException("Not enough stock for product {$this->id} at warehouse {$warehouseId}");
            }

            $stock->reserved += $qty;
            $stock->save();

            $this->syncAvailableStock();
        });
    }

    public function releaseReservedStock(int $qty, ?int $warehouseId = null): void
    {
        if ($qty <= 0) {
            return;
        }

        $warehouseId ??= Warehouse::getDefault()->id;

        DB::transaction(function () use ($qty, $warehouseId) {
            $stock = $this->stocks()
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                return;
            }

            $stock->reserved = max(0, $stock->reserved - $qty);
            $stock->save();

            $this->syncAvailableStock();
        });
    }

    public function commitReservedStock(int $qty, ?int $warehouseId = null): void
    {
        if ($qty <= 0) {
            return;
        }

        $warehouseId ??= Warehouse::getDefault()->id;

        DB::transaction(function () use ($qty, $warehouseId) {
            $stock = $this->stocks()
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new \DomainException("Not enough stock for product {$this->id} at warehouse {$warehouseId}");
            }

            if ($stock->reserved < $qty) {
                throw new \DomainException("Not enough reserved stock for product {$this->id} at warehouse {$warehouseId}");
            }

            if ($stock->qty < $qty) {
                throw new \DomainException("Not enough stock for product {$this->id} at warehouse {$warehouseId}");
            }

            $stock->reserved -= $qty;
            $stock->qty -= $qty;
            $stock->save();

            $this->syncAvailableStock();
        });
    }

    public function availableStock(?int $warehouseId = null): int
    {
        if ($warehouseId === null) {
            return (int) $this->stock;
        }

        $stock = $this->relationLoaded('stocks')
            ? $this->stocks->firstWhere('warehouse_id', $warehouseId)
            : $this->stocks()->where('warehouse_id', $warehouseId)->first();

        if (! $stock) {
            return 0;
        }

        return max(0, (int) $stock->qty - (int) $stock->reserved);
    }

    public function syncAvailableStock(): void
    {
        $total = (int) $this->stocks()
            ->selectRaw('COALESCE(SUM(qty - reserved), 0) as aggregate_available')
            ->value('aggregate_available');

        $this->forceFill(['stock' => max(0, $total)])->saveQuietly();
    }

    protected function stockForUpdate(int $warehouseId): ProductStock
    {
        $query = $this->stocks()->where('warehouse_id', $warehouseId);

        $stock = $query->lockForUpdate()->first();

        if (! $stock) {
            $this->stocks()->create([
                'warehouse_id' => $warehouseId,
                'qty' => 0,
                'reserved' => 0,
            ]);

            $stock = $query->lockForUpdate()->first();
        }

        if (! $stock) {
            throw new \RuntimeException('Unable to obtain stock row.');
        }

        return $stock;
    }


    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->oldest('id');
    }

// щоб не ловити N+1 і аксесор працював без додаткових запитів
    protected $with = ['images', 'vendor'];

// якщо хочеш бачити поле як атрибут (не обов'язково для таблиці)
    protected $appends = ['preview_url'];

    public function getPreviewUrlAttribute(): ?string
    {
        // колекцію сортуємо один раз, без подвійних sortBy
        $img = $this->images->sortBy([
            ['is_primary', 'desc'],
            ['sort', 'asc'],
            ['id', 'asc'],
        ])->first();

        if (! $img || blank($img->path)) {
            return null;
        }

        $disk = $img->disk ?: 'public';
        return \Storage::disk($disk)->url($img->path);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool)$this->is_active && (int)$this->stock > 0;
    }

    public function coverImage(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->orderBy('sort')->first();
    }

    public function refreshRating(): void
    {
        $aggregate = $this->reviews()
            ->approved()
            ->selectRaw('COUNT(*) as aggregate_count, AVG(rating) as aggregate_avg')
            ->toBase()
            ->first();

        $count = (int) ($aggregate->aggregate_count ?? 0);
        $avg = $aggregate->aggregate_avg;

        $this->forceFill([
            'reviews_count' => $count,
            'rating' => $count > 0 && $avg !== null ? round((float) $avg, 2) : null,
        ])->saveQuietly();
    }

    public function getCoverUrlAttribute(): ?string
    {
        $p = $this->coverImage()?->path;
        return $p ? Storage::disk('public')->url($p) : null;
    }

    public function toSearchableArray(): array
    {
        $attributes = $this->attributeDefinitions();

        $nameTranslations = $this->name_translations ?? [];
        $descriptionTranslations = $this->description_translations ?? [];
        $defaultLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);
        $supportedLocales = config('app.supported_locales');
        if (!is_array($supportedLocales) || $supportedLocales === []) {
            $supportedLocales = [$defaultLocale];
        }

        $defaultName = parent::getAttribute('name');
        if ($defaultName === null && isset($nameTranslations[$defaultLocale])) {
            $defaultName = $nameTranslations[$defaultLocale];
        }

        $defaultDescription = parent::getAttribute('description');
        if ($defaultDescription === null && isset($descriptionTranslations[$defaultLocale])) {
            $defaultDescription = $descriptionTranslations[$defaultLocale];
        }

        $attrs = $this->buildSearchableAttributes($attributes, $supportedLocales, $defaultLocale, $fallbackLocale);

        $payload = [
            'id' => $this->id,
            'name' => $defaultName,
            'name_translations' => $nameTranslations,
            'description' => $defaultDescription,
            'description_translations' => $descriptionTranslations,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'stock' => (int) $this->stock,
            'price' => (float) $this->price,
            'is_active' => (bool) $this->is_active,
            'attrs' => $attrs,
        ];

        foreach ($supportedLocales as $locale) {
            $payload["name_{$locale}"] = $nameTranslations[$locale]
                ?? ($fallbackLocale && isset($nameTranslations[$fallbackLocale]) ? $nameTranslations[$fallbackLocale] : $defaultName);
            $payload["description_{$locale}"] = $descriptionTranslations[$locale]
                ?? ($fallbackLocale && isset($descriptionTranslations[$fallbackLocale]) ? $descriptionTranslations[$fallbackLocale] : $defaultDescription);
        }

        return $payload;
    }

    public function attributeDefinitions(): array
    {
        $raw = $this->getAttribute('attributes');

        if (!is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $item) {
            if (!is_array($item) || !isset($item['key'], $item['value'])) {
                continue;
            }

            $translations = array_filter(
                (array) ($item['translations'] ?? []),
                fn ($value) => is_string($value) && $value !== ''
            );

            $normalized[] = [
                'key' => (string) $item['key'],
                'value' => (string) $item['value'],
                'translations' => $translations,
            ];
        }

        return $normalized;
    }

    private function buildSearchableAttributes(array $attributes, array $locales, string $defaultLocale, ?string $fallbackLocale): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $key = $attribute['key'];
            $value = $attribute['value'];
            $translations = $attribute['translations'] ?? [];

            $result[$key] = $value;

            foreach ($locales as $locale) {
                $label = $translations[$locale]
                    ?? ($fallbackLocale && isset($translations[$fallbackLocale]) ? $translations[$fallbackLocale] : null)
                    ?? ($translations[$defaultLocale] ?? null);

                if ($label !== null && $label !== '') {
                    $result["{$key}_{$locale}"] = $label;
                }
            }
        }

        return $result;
    }
}
