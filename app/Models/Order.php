<?php

namespace App\Models;

use App\Jobs\SendOrderStatusMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'email', 'status', 'total',
        'shipping_address', 'billing_address', 'note', 'number',
        'shipping_address_id',
        'currency','payment_intent_id','payment_status','paid_at','inventory_committed_at'
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

    protected $attributes = [
        'total' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->status ??= OrderStatus::New->value;
            if (empty($order->email) && $order->user_id && $order->user?->email) {
                $order->email = $order->user->email;
            }
            if (blank($order->number)) {
                $order->number = $order->makeOrderNumber();
            }
            if (is_null($order->total)) {
                $order->total = 0;
            }
           // $order->loadCount('items');
        });
        static::updated(function (Order $order) {
            $order->recalculateTotal();
        });
    }

    public function makeOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(16));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function canTransitionTo(OrderStatus $to): bool
    {
        return match ($this->status) {
            OrderStatus::New => in_array($to, [OrderStatus::Paid, OrderStatus::Cancelled], true),
            OrderStatus::Paid => in_array($to, [OrderStatus::Shipped, OrderStatus::Cancelled], true),
            OrderStatus::Shipped => false,
            OrderStatus::Cancelled => false,
        };
    }

    public function transitionTo(OrderStatus $to): void
    {
        if (!$this->canTransitionTo($to)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$to->value}");
        }

        DB::transaction(function () use ($to) {
            if ($to === OrderStatus::Paid && $this->status === OrderStatus::New) {
                $this->items()->with('product')->lockForUpdate()->get()->each(function ($item) {
                    $item->product->adjustStock(-$item->qty);
                });
            }
            if ($to === OrderStatus::Cancelled && $this->status === OrderStatus::Paid) {
                $this->items()->with('product')->lockForUpdate()->get()->each(function ($item) {
                    $item->product->adjustStock($item->qty);
                });
            }

            $this->update(['status' => $to]);

            match ($to) {
                OrderStatus::Shipped => $this->syncShipment([
                    'status' => ShipmentStatus::Shipped,
                    'shipped_at' => now(),
                ]),
                OrderStatus::Cancelled => $this->syncShipment([
                    'status' => ShipmentStatus::Cancelled,
                    'shipped_at' => null,
                    'delivered_at' => null,
                ]),
                default => null,
            };
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
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
                if (!$product) {
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
        $this->transitionTo(OrderStatus::Shipped);
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
            $this->syncShipment([
                'status' => ShipmentStatus::Cancelled,
                'shipped_at' => null,
                'delivered_at' => null,
            ]);
        });
    }


    public function recalculateTotal(): void
    {
        $total = (float)($this->items()
            ->selectRaw('COALESCE(SUM(qty * price), 0) AS t')
            ->value('t') ?? 0);

        $this->forceFill(['total' => $total])->saveQuietly();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest('id');
    }

    protected function syncShipment(array $attributes, bool $createIfMissing = true): void
    {
        $shipment = $this->shipment;

        if (! $shipment && ! $createIfMissing) {
            return;
        }

        if (! $shipment) {
            $shipment = $this->shipment()->make();
        }

        $shipment->fill(array_merge([
            'address_id' => $this->shipping_address_id,
        ], $attributes));

        $shipment->save();

        $this->setRelation('shipment', $shipment);
    }
}
