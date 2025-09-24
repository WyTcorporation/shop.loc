<?php

namespace App\Observers;

use App\Enums\ShipmentStatus;
use App\Models\OrderStatusLog;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class ShipmentObserver
{
    public bool $afterCommit = true;

    public function updated(Shipment $shipment): void
    {
        if (! $shipment->wasChanged('status')) {
            return;
        }

        if (! $shipment->order_id) {
            return;
        }

        $originalStatus = $shipment->getOriginal('status');
        $fromEnum = $originalStatus instanceof ShipmentStatus
            ? $originalStatus
            : (is_string($originalStatus) ? ShipmentStatus::tryFrom($originalStatus) : null);
        $from = $fromEnum?->value ?? (is_string($originalStatus) ? $originalStatus : null);

        $status = $shipment->getAttribute('status');
        $toEnum = $status instanceof ShipmentStatus ? $status : ShipmentStatus::from((string) $status);
        $to = $toEnum->value;

        OrderStatusLog::create([
            'order_id' => $shipment->order_id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => Auth::id(),
            'note' => __('shop.orders.logs.shipment_status_note', [
                'status' => __('shop.orders.shipment_status.' . $toEnum->value),
            ]),
        ]);

    }
}
