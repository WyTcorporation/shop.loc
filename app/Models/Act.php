<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentAuditing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Act extends Model
{
    use HasFactory;
    use HasDocumentAuditing;

    protected $fillable = [
        'order_id',
        'number',
        'issued_at',
        'status',
        'total',
        'description',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'total' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'draft',
        'total' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function toExportArray(): array
    {
        $order = $this->order;

        return [
            'document_type' => 'act',
            'number' => $this->number,
            'order_number' => $order?->number,
            'issued_at' => $this->issued_at?->toDateString(),
            'status' => $this->status,
            'total' => (string) $this->total,
            'description' => $this->description,
            'customer' => $order?->user?->name ?? $order?->email,
        ];
    }
}
