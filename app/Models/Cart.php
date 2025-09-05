<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'status'];
    protected $attributes = ['status' => 'active'];

//    protected static function booted(): void
//    {
//        static::creating(function (self $m) {
//            if (!$m->getKey()) {
//                $m->setAttribute($m->getKeyName(), (string)Str::uuid());
//            }
//            $m->status ??= 'active';
//        });
//    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
