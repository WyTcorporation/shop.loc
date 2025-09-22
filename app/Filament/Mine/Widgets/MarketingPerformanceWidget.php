<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MarketingPerformanceWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public ?string $filter = DashboardPeriod::ThirtyDays->value;

    protected function getHeading(): ?string
    {
        return __('shop.widgets.marketing_performance.title');
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
        $metrics = app(DashboardMetricsService::class)->getMarketingPerformance($period);

        return [
            Stat::make(__('shop.widgets.marketing_performance.stats.email_opens'), number_format($metrics['email']['opens'] ?? 0))
                ->description(__('shop.widgets.marketing_performance.descriptions.avg_conversion', ['rate' => number_format($metrics['email']['average_conversion_rate'] ?? 0, 2)]))
                ->color('primary'),
            Stat::make(__('shop.widgets.marketing_performance.stats.push_clicks'), number_format($metrics['push']['clicks'] ?? 0))
                ->description(__('shop.widgets.marketing_performance.descriptions.avg_conversion', ['rate' => number_format($metrics['push']['average_conversion_rate'] ?? 0, 2)]))
                ->color('info'),
            Stat::make(__('shop.widgets.marketing_performance.stats.total_conversions'), number_format($metrics['total_conversions'] ?? 0))
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->hasAnyPermission([
            Permission::ViewMarketing->value,
            Permission::ManageMarketing->value,
            Permission::ViewCampaigns->value,
            Permission::ManageCampaigns->value,
            Permission::ViewSegments->value,
            Permission::ManageSegments->value,
            Permission::ViewCampaignTemplates->value,
            Permission::ManageCampaignTemplates->value,
            Permission::ViewCampaignTests->value,
            Permission::ManageCampaignTests->value,
        ]) ?? false;
    }
}
