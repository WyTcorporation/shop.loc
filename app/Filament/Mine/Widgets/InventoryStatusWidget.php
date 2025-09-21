<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InventoryStatusWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = ['@lg' => 6, '@xl' => 4];

    protected function getHeading(): ?string
    {
        return __('shop.admin.dashboard.inventory.title');
    }

    protected function getStats(): array
    {
        $metrics = app(DashboardMetricsService::class)->getInventoryStatus();

        return [
            Stat::make(__('shop.admin.dashboard.inventory.skus'), number_format($metrics['skus']))
                ->color('primary'),
            Stat::make(__('shop.admin.dashboard.inventory.available_units'), number_format($metrics['available_units']))
                ->color('success'),
            Stat::make(
                __('shop.admin.dashboard.inventory.low_stock', ['threshold' => $metrics['threshold']]),
                number_format($metrics['low_stock'])
            )->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->can(Permission::ManageInventory->value) ?? false;
    }
}
