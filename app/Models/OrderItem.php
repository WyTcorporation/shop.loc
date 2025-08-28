<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'qty', 'price'];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
    ];

    protected $touches = ['order'];

    protected static function booted(): void
    {
        static::saved(fn ($item) => $item->order?->recalculateTotal());
        static::created(fn ($item) => $item->order?->recalculateTotal());
        static::updated(fn ($item) => $item->order?->recalculateTotal());
        static::deleted(fn ($item) => $item->order?->recalculateTotal());
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
