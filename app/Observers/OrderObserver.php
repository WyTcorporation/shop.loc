<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use BackedEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public bool $afterCommit = true;

    /**
     * @var array<int|string, string|null>
     */
    private static array $previousStatuses = [];

    public function updating(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        $cacheKey = $this->cacheKey($order);

        if (array_key_exists($cacheKey, self::$previousStatuses)) {
            return;
        }

        $originalStatus = $order->getOriginal('status');

        if ($originalStatus instanceof BackedEnum) {
            self::$previousStatuses[$cacheKey] = $originalStatus->value;

            return;
        }

        if (is_string($originalStatus) || $originalStatus === null) {
            self::$previousStatuses[$cacheKey] = $originalStatus;

            return;
        }

        self::$previousStatuses[$cacheKey] = (string) $originalStatus;
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $cacheKey = $this->cacheKey($order);
        $stored = self::$previousStatuses[$cacheKey] ?? null;
        unset(self::$previousStatuses[$cacheKey]);

        $originalStatus = $stored ?? $order->getOriginal('status');

        if ($originalStatus instanceof BackedEnum) {
            $fromEnum = OrderStatus::tryFrom($originalStatus->value);
            $from = $fromEnum?->value;
        } elseif (is_string($originalStatus)) {
            $fromEnum = OrderStatus::tryFrom($originalStatus);
            $from = $fromEnum?->value ?? $originalStatus;
        } else {
            $fromEnum = null;
            $from = null;
        }

        $status = $order->getAttribute('status');
        $toEnum = $status instanceof OrderStatus ? $status : OrderStatus::from((string) $status);
        $to = $toEnum->value;

        OrderStatusLog::create([
            'order_id' => $order->getKey(),
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => Auth::id(),
            'note' => $this->resolveStatusNote(),
        ]);
    }

    private function cacheKey(Order $order): int|string
    {
        return $order->getKey() ?? spl_object_id($order);
    }

    private function resolveStatusNote(): ?string
    {
        $note = null;

        $request = request();
        if ($request) {
            $note = $request->input('status_note');
        }

        if (is_array($note)) {
            $note = Arr::get($note, 'note');
        }

        $note = is_string($note) ? trim($note) : null;

        return $note === '' ? null : $note;
    }
}
