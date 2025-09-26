<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('shop.admin.resources.invoices.statuses.draft'),
            self::Issued => __('shop.admin.resources.invoices.statuses.issued'),
            self::Paid => __('shop.admin.resources.invoices.statuses.paid'),
            self::Void => __('shop.admin.resources.invoices.statuses.void'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Issued => 'info',
            self::Paid => 'success',
            self::Void => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->toArray();
    }
}
