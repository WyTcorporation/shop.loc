<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty',
        'reserved',
    ];

    protected $casts = [
        'qty' => 'integer',
        'reserved' => 'integer',
    ];

    protected $appends = ['available'];

    protected static function booted(): void
    {
        $sync = function (ProductStock $stock): void {
            $stock->product?->syncAvailableStock();
        };

        static::saved($sync);
        static::deleted($sync);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getAvailableAttribute(): int
    {
        return max(0, (int) $this->qty - (int) $this->reserved);
    }
}
