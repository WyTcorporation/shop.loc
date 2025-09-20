<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;



class ProductImage extends Model
{
    use HasFactory;
    use HasTranslations {
        HasTranslations::initializeHasTranslations as protected initializeTranslationsTrait;
    }

    protected $appends = ['url'];

    protected $fillable = ['product_id', 'disk', 'path', 'alt','alt_translations','sort','is_primary'];

    protected $casts = [
        'sort' => 'integer',
        'alt_translations' => 'array',
    ];

    public function initializeHasTranslations(): void
    {
        $this->translatable = ['alt'];
        $this->initializeTranslationsTrait();
    }

    protected static function booted(): void
    {
        static::saving(function (self $image) {
            $image->disk = $image->disk ?: static::defaultDisk();
        });

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
        $disk = $this->disk ?: static::defaultDisk();

        return Storage::disk($disk)->url($this->path);
    }

    public static function defaultDisk(): string
    {
        return config('shop.product_images_disk', 'public');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
