<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => optional($this->category)->name,
            'price' => (float)$this->price,
            'is_active' => $this->is_active,
        ];
    }
}
