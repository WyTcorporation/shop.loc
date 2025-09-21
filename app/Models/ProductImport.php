<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_name',
        'file_path',
        'disk',
        'status',
        'total_rows',
        'processed_rows',
        'created_rows',
        'updated_rows',
        'failed_rows',
        'batch_id',
        'meta',
        'message',
        'completed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductImportLog::class);
    }
}
