<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'text',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted(): void
    {
        $syncProductRating = function (Review $review): void {
            if ($product = $review->product) {
                $product->refreshRating();
            }
        };

        static::saved($syncProductRating);
        static::deleted($syncProductRating);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
