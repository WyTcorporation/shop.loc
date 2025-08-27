<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStats extends BaseWidget
{
    protected function getStats(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = $counts->sum();
        $get = fn(OrderStatus $s) => $counts[$s->value] ?? 0;

        return [
            Stat::make('Orders', (string)$total)->description('total'),
            Stat::make('New', (string)$get(OrderStatus::New))->color('warning'),
            Stat::make('Paid', (string)$get(OrderStatus::Paid))->color('success'),
            Stat::make('Shipped', (string)$get(OrderStatus::Shipped)),
            Stat::make('Canceled', (string)$get(OrderStatus::Cancelled))->color('danger'),
        ];
    }
}
