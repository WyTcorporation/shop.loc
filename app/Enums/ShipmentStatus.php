<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('shop.orders.shipment_status.pending'),
            self::Processing => __('shop.orders.shipment_status.processing'),
            self::Shipped => __('shop.orders.shipment_status.shipped'),
            self::Delivered => __('shop.orders.shipment_status.delivered'),
            self::Cancelled => __('shop.orders.shipment_status.cancelled'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Processing => 'warning',
            self::Shipped => 'info',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
