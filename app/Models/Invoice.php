<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasDocumentAuditing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;
    use HasDocumentAuditing;

    protected $fillable = [
        'order_id',
        'number',
        'issued_at',
        'due_at',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'metadata',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'issued_at' => 'date',
        'due_at' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'subtotal' => 0,
        'tax_total' => 0,
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
            'document_type' => 'invoice',
            'number' => $this->number,
            'order_number' => $order?->number,
            'issued_at' => $this->issued_at?->toDateString(),
            'due_at' => $this->due_at?->toDateString(),
            'status' => $this->status?->value,
            'currency' => $this->currency ?? $order?->currency,
            'subtotal' => (string) $this->subtotal,
            'tax_total' => (string) $this->tax_total,
            'total' => (string) $this->total,
            'customer' => $order?->user?->name ?? $order?->email,
            'metadata' => $this->metadata ?? [],
        ];
    }
}
