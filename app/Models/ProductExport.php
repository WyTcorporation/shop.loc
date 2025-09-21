<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'format',
        'file_name',
        'file_path',
        'disk',
        'status',
        'total_rows',
        'processed_rows',
        'filters',
        'message',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
