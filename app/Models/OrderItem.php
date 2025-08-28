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
        $recalc = fn ($item) => $item->order?->recalculateTotal();
        static::created($recalc);
        static::updated($recalc);
        static::deleted($recalc);
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
