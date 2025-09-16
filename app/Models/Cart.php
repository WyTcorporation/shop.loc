<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'status', 'coupon_id', 'coupon_code', 'loyalty_points_used'];
    protected $attributes = ['status' => 'active', 'loyalty_points_used' => 0];

    protected $casts = [
        'loyalty_points_used' => 'integer',
    ];

//    protected static function booted(): void
//    {
//        static::creating(function (self $m) {
//            if (!$m->getKey()) {
//                $m->setAttribute($m->getKeyName(), (string)Str::uuid());
//            }
//            $m->status ??= 'active';
//        });
//    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
