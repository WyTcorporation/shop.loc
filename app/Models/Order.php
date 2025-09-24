<?php

namespace App\Models;

use App\Jobs\SendOrderStatusMail;
use App\Support\Phone;
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
        'shipping_address_id', 'subtotal', 'discount_total', 'coupon_id',
        'coupon_code', 'coupon_discount', 'loyalty_points_used', 'loyalty_points_value',
        'loyalty_points_earned',
        'currency', 'locale', 'payment_intent_id', 'payment_status', 'paid_at', 'inventory_committed_at'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'loyalty_points_value' => 'decimal:2',
        'loyalty_points_used' => 'integer',
        'loyalty_points_earned' => 'integer',
        'locale' => 'string',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'inventory_committed_at' => 'datetime',
    ];

    protected $attributes = [
        'total' => 0,
        'subtotal' => 0,
        'discount_total' => 0,
        'coupon_discount' => 0,
        'loyalty_points_used' => 0,
        'loyalty_points_value' => 0,
        'loyalty_points_earned' => 0,
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
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
                $this->reserveInventory();
            }

            if ($to === OrderStatus::Cancelled && $this->inventoryCommitted()) {
                $this->releaseInventory();
            }

            if ($to === OrderStatus::Shipped) {
                $this->commitReservedInventory();
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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest('created_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function acts(): HasMany
    {
        return $this->hasMany(Act::class);
    }

    public function saftExportLogs(): HasMany
    {
        return $this->hasMany(SaftExportLog::class);
    }

    public function involvesVendor(int $vendorId): bool
    {
        if ($this->relationLoaded('items')) {
            $items = $this->items;

            if ($items->contains(function ($item) use ($vendorId) {
                if (! $item->relationLoaded('product')) {
                    return false;
                }

                return (int) ($item->product?->vendor_id ?? 0) === $vendorId;
            })) {
                return true;
            }
        }

        return $this->items()
            ->whereHas('product', fn ($query) => $query->where('vendor_id', $vendorId))
            ->exists();
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
            $currentStatus = $this->currentStatus();

            if ($currentStatus !== OrderStatus::New) {
                throw new \RuntimeException(__('shop.orders.errors.only_new_can_be_marked_paid', [
                    'required' => $this->localizedStatus(OrderStatus::New),
                    'status' => $this->localizedStatus($currentStatus),
                    'number' => $this->orderNumber(),
                ]));
            }
            $this->reserveInventory();
            $this->update(['status' => OrderStatus::Paid]);
        });
    }

    /** @throws \RuntimeException */
    public function markShipped(): void
    {
        $currentStatus = $this->currentStatus();

        if ($currentStatus !== OrderStatus::Paid) {
            throw new \RuntimeException(__('shop.orders.errors.only_paid_can_be_marked_shipped', [
                'required' => $this->localizedStatus(OrderStatus::Paid),
                'status' => $this->localizedStatus($currentStatus),
                'number' => $this->orderNumber(),
            ]));
        }
        $this->transitionTo(OrderStatus::Shipped);
    }

    /** @throws \RuntimeException */
    public function cancel(): void
    {
        DB::transaction(function () {
            $currentStatus = $this->currentStatus();

            if (! in_array($currentStatus, [OrderStatus::New, OrderStatus::Paid], true)) {
                throw new \RuntimeException(__('shop.orders.errors.only_new_or_paid_can_be_cancelled', [
                    'allowed' => $this->localizedStatuses([OrderStatus::New, OrderStatus::Paid]),
                    'status' => $this->localizedStatus($currentStatus),
                    'number' => $this->orderNumber(),
                ]));
            }
            if ($this->inventoryCommitted()) {
                $this->releaseInventory();
            }

            $this->update(['status' => OrderStatus::Cancelled]);
            $this->syncShipment([
                'status' => ShipmentStatus::Cancelled,
                'shipped_at' => null,
                'delivered_at' => null,
            ]);
        });
    }


    public function reserveInventory(): void
    {
        if ($this->inventoryCommitted()) {
            return;
        }

        $items = $this->items()->with('product')->lockForUpdate()->get();

        if ($items->isEmpty()) {
            $this->forceFill(['inventory_committed_at' => now()])->saveQuietly();
            return;
        }

        $defaultWarehouseId = Warehouse::getDefault()->id;

        foreach ($items as $item) {
            $product = $item->product;

            if (! $product) {
                throw new \RuntimeException('Order item without product.');
            }

            $warehouseId = $item->warehouse_id ?? $defaultWarehouseId;

            if (! $item->warehouse_id) {
                $item->forceFill(['warehouse_id' => $warehouseId])->saveQuietly();
            }

            try {
                $product->reserveStock($item->qty, $warehouseId);
            } catch (\DomainException $e) {
                throw new \RuntimeException($e->getMessage(), 0, $e);
            }
        }

        $this->forceFill(['inventory_committed_at' => now()])->saveQuietly();
    }

    public function releaseInventory(): void
    {
        if (! $this->inventoryCommitted()) {
            return;
        }

        $items = $this->items()->with('product')->lockForUpdate()->get();

        $defaultWarehouseId = Warehouse::getDefault()->id;

        foreach ($items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            $warehouseId = $item->warehouse_id ?? $defaultWarehouseId;

            $product->releaseReservedStock($item->qty, $warehouseId);
        }

        $this->forceFill(['inventory_committed_at' => null])->saveQuietly();
    }

    public function commitReservedInventory(): void
    {
        if (! $this->inventoryCommitted()) {
            $this->reserveInventory();
        }

        $items = $this->items()->with('product')->lockForUpdate()->get();

        if ($items->isEmpty()) {
            $this->forceFill(['inventory_committed_at' => null])->saveQuietly();
            return;
        }

        $defaultWarehouseId = Warehouse::getDefault()->id;

        foreach ($items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            $warehouseId = $item->warehouse_id ?? $defaultWarehouseId;

            try {
                $product->commitReservedStock($item->qty, $warehouseId);
            } catch (\DomainException $e) {
                throw new \RuntimeException($e->getMessage(), 0, $e);
            }
        }

        $this->forceFill(['inventory_committed_at' => null])->saveQuietly();
    }


    public function recalculateTotal(): void
    {
        $subtotal = (float)($this->items()
            ->selectRaw('COALESCE(SUM(qty * price), 0) AS t')
            ->value('t') ?? 0);

        $discount = round((float)($this->coupon_discount ?? 0) + (float)($this->loyalty_points_value ?? 0), 2);
        $total = max(0, round($subtotal - $discount, 2));

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'total' => $total,
        ])->saveQuietly();
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

    private function currentStatus(): OrderStatus
    {
        return $this->status instanceof OrderStatus
            ? $this->status
            : OrderStatus::from((string) $this->status);
    }

    private function localizedStatus(OrderStatus $status): string
    {
        return __('shop.orders.statuses.' . $status->value);
    }

    /**
     * @param OrderStatus[] $statuses
     */
    private function localizedStatuses(array $statuses): string
    {
        return implode(', ', array_map(fn (OrderStatus $status) => $this->localizedStatus($status), $statuses));
    }

    private function orderNumber(): string
    {
        return (string) ($this->number ?? $this->id);
    }

    protected function getShippingAddressAttribute($value): ?array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return self::formatAddress($decoded);
    }

    protected function setShippingAddressAttribute($value): void
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $normalized = self::normalizeAddress($decoded);

        $this->attributes['shipping_address'] = is_null($normalized)
            ? null
            : json_encode($normalized);
    }

    protected function getBillingAddressAttribute($value): ?array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return self::formatAddress($decoded);
    }

    protected function setBillingAddressAttribute($value): void
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $normalized = self::normalizeAddress($decoded);

        $this->attributes['billing_address'] = is_null($normalized)
            ? null
            : json_encode($normalized);
    }

    private static function formatAddress(?array $address): ?array
    {
        if (! is_array($address)) {
            return $address;
        }

        if (array_key_exists('phone', $address)) {
            $address['phone'] = Phone::format($address['phone']);
        }

        return $address;
    }

    private static function normalizeAddress(?array $address): ?array
    {
        if (! is_array($address)) {
            return $address;
        }

        if (array_key_exists('phone', $address)) {
            $address['phone'] = Phone::normalize($address['phone']);
        }

        return $address;
    }
}
