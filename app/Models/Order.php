<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'email', 'status', 'total',
        'shipping_address', 'billing_address', 'note', 'number',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'inventory_committed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->status ??= OrderStatus::New->value;
            $order->number ??= 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(16));
            if (empty($order->email) && $order->user_id && $order->user?->email) {
                $order->email = $order->user->email;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canTransitionTo(OrderStatus $to): bool
    {
        return match ($this->status) {
            OrderStatus::New      => in_array($to, [OrderStatus::Paid, OrderStatus::Cancelled], true),
            OrderStatus::Paid     => in_array($to, [OrderStatus::Shipped, OrderStatus::Cancelled], true),
            OrderStatus::Shipped  => false,
            OrderStatus::Cancelled => false,
        };
    }

    public function transitionTo(OrderStatus $to): void
    {
        if (! $this->canTransitionTo($to)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$to->value}");
        }

        DB::transaction(function () use ($to) {
            if ($to === OrderStatus::Paid && $this->status === OrderStatus::New) {
                foreach ($this->items as $item) {
                    $item->product->adjustStock(-$item->qty);
                }
            }
            if ($to === OrderStatus::Cancelled && $this->status === OrderStatus::Paid) {
                foreach ($this->items as $item) {
                    $item->product->adjustStock($item->qty);
                }
            }
            $this->update(['status' => $to]);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isShipped(): bool
    {
        return !is_null($this->shipped_at);
    }

    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    public function inventoryCommitted(): bool
    {
        return !is_null($this->inventory_committed_at);
    }

    public function canMarkPaid(): bool
    {
        return $this->status === OrderStatus::New->value;
    }

    public function canMarkShipped(): bool
    {
        return $this->status === OrderStatus::Paid->value;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            OrderStatus::New->value,
            OrderStatus::Paid->value,
        ], true);
    }

    public function markPaid(): void
    {
        if (! $this->canMarkPaid()) return;
        $this->forceFill(['status' => OrderStatus::Paid->value])->save();
        // TODO: paid_at, payment_id, нотифікації
    }

    public function markShipped(): void
    {
        if (! $this->canMarkShipped()) return;
        $this->forceFill(['status' => OrderStatus::Shipped->value])->save();
        // TODO: списання складу / трек-номер / нотифікації
    }

    public function cancel(): void
    {
        if (! $this->canCancel()) return;
        $this->forceFill(['status' => OrderStatus::Cancelled->value])->save();
        // TODO: повернення на склад / рефанд / нотифікації
    }

    public function recalculateTotal(): void
    {
        $total = (float) $this->items()
            ->selectRaw('COALESCE(SUM(qty * price), 0) as total')
            ->value('total');
        $this->forceFill(['total' => $total])->saveQuietly();
    }
}
