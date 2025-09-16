<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'category_id',
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
    ];


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
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
    protected $with = ['images'];

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
        $attrs = (array) $this->getAttribute('attributes');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'stock' => (int)$this->stock,
            'price' => (float)$this->price,
            'is_active' => (bool)$this->is_active,
            'attrs' => $attrs
        ];
    }
}
