<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'document_id',
        'order_id',
        'user_id',
        'event',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
