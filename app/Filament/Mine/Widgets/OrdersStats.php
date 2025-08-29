<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStats extends BaseWidget
{
    // автооновлення (опційно)
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // Кількості по статусах (з enum)
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) AS c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $get = fn (OrderStatus $s) => (int) ($counts[$s->value] ?? 0);

        // Спарклайн: сума замовлень за 14 днів
        $from = now()->startOfDay()->subDays(13);

        // PG: приводимо SUM(total) до float, групуємо по даті
        $byDay = Order::query()
            ->selectRaw('DATE(created_at) AS d, SUM(total)::float AS s')
            ->where('created_at', '>=', $from)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('s', 'd');

        $labels = collect(range(0, 13))
            ->map(fn ($i) => $from->copy()->addDays($i)->toDateString());

        $chart = $labels->map(fn ($d) => (float) ($byDay[$d] ?? 0))->all();

        return [
            Stat::make('New', (string) $get(OrderStatus::New))
                ->description('Awaiting')
                ->color(OrderStatus::New->badgeColor())
                ->chart($chart),

            Stat::make('Paid', (string) $get(OrderStatus::Paid))
                ->color(OrderStatus::Paid->badgeColor())
                ->chart($chart),

            Stat::make('Shipped', (string) $get(OrderStatus::Shipped))
                ->color(OrderStatus::Shipped->badgeColor())
                ->chart($chart),

            // (опція) Показати скасовані без графіка:
             Stat::make('Canceled', (string) $get(OrderStatus::Cancelled))
                 ->color(OrderStatus::Cancelled->badgeColor()),
        ];
    }
}
