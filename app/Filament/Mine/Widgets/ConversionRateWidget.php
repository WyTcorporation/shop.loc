<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ConversionRateWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = ['@lg' => 6, '@xl' => 4];

    public ?string $filter = DashboardPeriod::ThirtyDays->value;

    protected function getHeading(): ?string
    {
        return __('shop.admin.dashboard.conversion.title');
    }

    protected function getDescription(): ?string
    {
        $period = DashboardPeriod::tryFromFilter($this->filter);

        return $period->label();
    }

    protected function getFilters(): ?array
    {
        return DashboardPeriod::options();
    }

    protected function getStats(): array
    {
        $period = DashboardPeriod::tryFromFilter($this->filter);
        $summary = app(DashboardMetricsService::class)->getConversionSummary($period);

        return [
            Stat::make(__('shop.admin.dashboard.conversion.rate'), number_format($summary['rate'], 2) . '%')
                ->description(__('shop.admin.dashboard.conversion.rate_help'))
                ->color('success'),
            Stat::make(__('shop.admin.dashboard.conversion.orders'), number_format($summary['orders']))
                ->color('primary'),
            Stat::make(__('shop.admin.dashboard.conversion.carts'), number_format($summary['carts']))
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->can(Permission::ViewOrders->value) ?? false;
    }
}
