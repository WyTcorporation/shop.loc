<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New       = 'new';
    case Paid      = 'paid';
    case Shipped   = 'shipped';
    case Cancelled = 'cancelled';

    public function badgeColor(): string
    {
        return match ($this) {
            self::New       => 'warning',
            self::Paid      => 'success',
            self::Shipped   => 'info',
            self::Cancelled => 'danger',
        };
    }
}
