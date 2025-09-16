<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{

    use HasFactory;

    public const CACHE_KEY_FLAT = 'categories:index:flat';
    public const CACHE_KEY_TREE = 'categories:index:tree';

    protected $fillable = ['name', 'slug', 'parent_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
    ];

    protected static function booted(): void
    {
        $clear = fn () => static::clearCache();

        static::created($clear);
        static::updated($clear);
        static::deleted($clear);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_FLAT);
        Cache::forget(self::CACHE_KEY_TREE);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
