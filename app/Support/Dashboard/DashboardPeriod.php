<?php

namespace App\Support\Dashboard;

use Carbon\CarbonImmutable;

enum DashboardPeriod: string
{
    case Today = 'today';
    case SevenDays = '7_days';
    case ThirtyDays = '30_days';
    case NinetyDays = '90_days';

    public function label(): string
    {
        return match ($this) {
            self::Today => __('shop.admin.dashboard.filters.today'),
            self::SevenDays => __('shop.admin.dashboard.filters.seven_days'),
            self::ThirtyDays => __('shop.admin.dashboard.filters.thirty_days'),
            self::NinetyDays => __('shop.admin.dashboard.filters.ninety_days'),
        };
    }

    public static function options(): array
    {
        $periods = [];

        foreach (self::cases() as $case) {
            $periods[$case->value] = $case->label();
        }

        return $periods;
    }

    public function range(): array
    {
        $now = CarbonImmutable::now()->endOfDay();

        return match ($this) {
            self::Today => [CarbonImmutable::now()->startOfDay(), $now],
            self::SevenDays => [CarbonImmutable::now()->subDays(6)->startOfDay(), $now],
            self::ThirtyDays => [CarbonImmutable::now()->subDays(29)->startOfDay(), $now],
            self::NinetyDays => [CarbonImmutable::now()->subDays(89)->startOfDay(), $now],
        };
    }

    public static function tryFromFilter(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::ThirtyDays;
    }

    public function daysDifference(): int
    {
        [$start, $end] = $this->range();

        return $start->diffInDays($end) + 1;
    }
}
