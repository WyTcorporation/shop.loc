<?php

namespace App\Observers;

use App\Enums\ShipmentStatus;
use App\Jobs\SendOrderStatusMail;
use App\Models\Shipment;

class ShipmentObserver
{
    public bool $afterCommit = true;

    public function updated(Shipment $shipment): void
    {
        if (! $shipment->wasChanged('status')) {
            return;
        }

        if ($shipment->status !== ShipmentStatus::Delivered) {
            return;
        }

        if (! $shipment->order_id) {
            return;
        }

        SendOrderStatusMail::dispatch(
            $shipment->order_id,
            ShipmentStatus::Delivered->value
        )->afterCommit();
    }
}
