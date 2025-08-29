<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;



class ProductImage extends Model
{
    use HasFactory;

    protected $appends = ['url'];

    protected $fillable = ['product_id', 'disk', 'path', 'alt','sort','is_primary'];

    protected $casts = [
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $image) {
            if ($image->is_primary) {
                static::where('product_id', $image->product_id)
                    ->when($image->exists, fn ($q) => $q->whereKeyNot($image->getKey()))
                    ->update(['is_primary' => false]);
            }
        });
        static::deleting(function ($img) {
            if ($img->path) Storage::disk($img->disk)->delete($img->path);
        });
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
