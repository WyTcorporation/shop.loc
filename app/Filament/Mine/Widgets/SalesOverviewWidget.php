<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use function formatCurrency;

class SalesOverviewWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public ?string $filter = DashboardPeriod::ThirtyDays->value;

    protected function getHeading(): ?string
    {
        return __('shop.admin.dashboard.sales.title');
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
        $metrics = app(DashboardMetricsService::class);
        $summary = $metrics->getSalesSummary($period);
        $trend = $metrics->getSalesTrend($period);
        $baseCurrency = $summary['base_currency'];

        return [
            Stat::make(__('shop.admin.dashboard.sales.revenue'), formatCurrency($summary['revenue'], $baseCurrency))
                ->chart($trend['values'])
                ->color('success'),
            Stat::make(__('shop.admin.dashboard.sales.orders'), number_format($summary['orders']))
                ->chart($trend['values'])
                ->color('primary'),
            Stat::make(__('shop.admin.dashboard.sales.average_order_value'), formatCurrency($summary['average_order_value'], $baseCurrency))
                ->chart($trend['values'])
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->can(Permission::ViewOrders->value) ?? false;
    }
}
