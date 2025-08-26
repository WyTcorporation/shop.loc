<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'disk', 'path', 'alt', 'sort'];

    protected $casts = [
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($img) {
            if ($img->path) \Storage::disk($img->disk)->delete($img->path);
        });
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
