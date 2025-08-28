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

    /** @throws \RuntimeException */
    public function markPaid(): void
    {
        DB::transaction(function () {
            if ($this->status !== OrderStatus::New) {
                throw new \RuntimeException('Only NEW orders can be marked paid.');
            }
            $this->items()->with('product')->lockForUpdate()->get()->each(function ($item) {
                $product = $item->product;
                if (! $product) {
                    throw new \RuntimeException('Order item without product.');
                }
                if ($product->stock < $item->qty) {
                    throw new \RuntimeException("Not enough stock for {$product->name}.");
                }
                $product->decrement('stock', $item->qty);
            });

            $this->update(['status' => OrderStatus::Paid]);
        });
    }

    /** @throws \RuntimeException */
    public function markShipped(): void
    {
        if ($this->status !== OrderStatus::Paid) {
            throw new \RuntimeException('Only PAID orders can be marked shipped.');
        }
        $this->update(['status' => OrderStatus::Shipped]);
    }

    /** @throws \RuntimeException */
    public function cancel(): void
    {
        DB::transaction(function () {
            if (in_array($this->status, [OrderStatus::Shipped, OrderStatus::Cancelled], true)) {
                throw new \RuntimeException('Cannot cancel shipped/canceled order.');
            }
            if ($this->status === OrderStatus::Paid) {
                $this->items()->with('product')->lockForUpdate()->get()->each(function ($item) {
                    $item->product?->increment('stock', $item->qty);
                });
            }

            $this->update(['status' => OrderStatus::Cancelled]);
        });
    }


    public function recalculateTotal(): void
    {
        $total = (float) ($this->items()
            ->selectRaw('COALESCE(SUM(qty * price), 0) AS t')
            ->value('t') ?? 0);

        $this->forceFill(['total' => $total])->saveQuietly();
    }
}
