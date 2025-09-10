<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

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
        'price_old',
        'is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'price_old' => 'decimal:2',
    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function adjustStock(int $delta): void
    {
        $new = $this->stock + $delta;
        if ($new < 0) {
            throw new \DomainException("Not enough stock for product {$this->id}");
        }

        $this->forceFill(['stock' => $new])->save();
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
