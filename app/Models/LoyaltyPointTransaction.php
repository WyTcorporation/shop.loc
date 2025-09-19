<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPointTransaction extends Model
{
    use HasFactory;

    public const TYPE_EARN = 'earn';
    public const TYPE_REDEEM = 'redeem';
    public const TYPE_ADJUST = 'adjustment';

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'amount',
        'description',
        'meta',
    ];

    protected $casts = [
        'points' => 'integer',
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function getLocalizedDescriptionAttribute(): string
    {
        $meta = $this->meta;

        if (is_array($meta) && isset($meta['key']) && is_string($meta['key']) && $meta['key'] !== '') {
            $translation = __($meta['key'], $meta);

            if ($translation !== $meta['key']) {
                return $translation;
            }
        }

        return (string) ($this->description ?? '');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
