<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Widgets\PieChartWidget;
use Illuminate\Support\Facades\Auth;

class TrafficSourcesChart extends PieChartWidget
{
    protected int|string|array $columnSpan = ['@lg' => 6, '@xl' => 4];

    public ?string $filter = DashboardPeriod::ThirtyDays->value;

    public function getHeading(): ?string
    {
        return __('shop.admin.dashboard.traffic.title');
    }

    protected function getFilters(): ?array
    {
        return DashboardPeriod::options();
    }

    protected function getData(): array
    {
        $period = DashboardPeriod::tryFromFilter($this->filter);
        $sources = app(DashboardMetricsService::class)->getTrafficSources($period);

        return [
            'datasets' => [
                [
                    'label' => __('shop.admin.dashboard.traffic.revenue'),
                    'data' => $sources->pluck('share')->map(fn ($value) => round((float) $value, 2))->all(),
                ],
            ],
            'labels' => $sources->pluck('channel')->all(),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->can(Permission::ViewOrders->value) ?? false;
    }
}
