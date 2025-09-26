<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'body',
        'meta',
        'read_at',
        'read_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function markAsRead(int|string $userId): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->forceFill([
            'read_at' => now(),
            'read_by' => $userId,
        ])->save();
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
