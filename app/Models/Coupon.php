<?php

namespace App\Models;

use App\Enums\OrderStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENT = 'percent';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_cart_total',
        'max_discount',
        'usage_limit',
        'per_user_limit',
        'used',
        'starts_at',
        'expires_at',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_cart_total' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'used' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function scopeActive(Builder $builder): Builder
    {
        $now = Carbon::now();

        return $builder
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $subtotal = max($subtotal, 0);

        $discount = match ($this->type) {
            self::TYPE_PERCENT => $subtotal * ((float) $this->value / 100),
            default => (float) $this->value,
        };

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return min($discount, $subtotal);
    }

    public function remainingUses(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used);
    }

    public function usageCountForUser(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return (int) $this->orders()
            ->where('user_id', $user->id)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->count();
    }
}
