<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentAuditing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNote extends Model
{
    use HasFactory;
    use HasDocumentAuditing;

    protected $fillable = [
        'order_id',
        'number',
        'issued_at',
        'dispatched_at',
        'status',
        'items',
        'remarks',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'dispatched_at' => 'date',
        'items' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function toExportArray(): array
    {
        $order = $this->order;

        return [
            'document_type' => 'delivery_note',
            'number' => $this->number,
            'order_number' => $order?->number,
            'issued_at' => $this->issued_at?->toDateString(),
            'dispatched_at' => $this->dispatched_at?->toDateString(),
            'status' => $this->status,
            'customer' => $order?->user?->name ?? $order?->email,
            'items' => $this->items ?? $order?->items?->map(fn ($item) => [
                'sku' => $item->sku,
                'name' => $item->name,
                'quantity' => $item->quantity,
            ])->all(),
            'remarks' => $this->remarks,
        ];
    }
}
