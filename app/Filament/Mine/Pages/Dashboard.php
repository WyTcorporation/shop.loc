<?php

namespace App\Filament\Mine\Pages;

use App\Filament\Mine\Widgets\ConversionRateWidget;
use App\Filament\Mine\Widgets\InventoryStatusWidget;
use App\Filament\Mine\Widgets\SalesOverviewWidget;
use App\Filament\Mine\Widgets\TopProductsTable;
use App\Filament\Mine\Widgets\TrafficSourcesChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
            ConversionRateWidget::class,
            TrafficSourcesChart::class,
            TopProductsTable::class,
            InventoryStatusWidget::class,
            AccountWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 12;
    }
}
