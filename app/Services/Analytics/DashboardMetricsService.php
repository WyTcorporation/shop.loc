<?php

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Services\Currency\CurrencyConverter;
use App\Services\Marketing\MarketingAnalyticsService;
use App\Support\Dashboard\DashboardPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly MarketingAnalyticsService $marketingAnalytics,
    )
    {
    }

    public function getBaseCurrency(): string
    {
        return $this->converter->getBaseCurrency();
    }

    public function getSalesSummary(DashboardPeriod $period): array
    {
        [$start, $end] = $period->range();

        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', [OrderStatus::Paid->value, OrderStatus::Shipped->value])
            ->get(['total', 'currency']);

        $revenue = $orders->sum(fn (Order $order) => $this->converter->convertToBase($order->total, $order->currency));
        $ordersCount = $orders->count();
        $averageOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0.0;

        return [
            'base_currency' => $this->getBaseCurrency(),
            'revenue' => round($revenue, 2),
            'orders' => $ordersCount,
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    public function getSalesTrend(DashboardPeriod $period): array
    {
        [$start, $end] = $period->range();
        $baseCurrency = $this->getBaseCurrency();

        $rows = Order::query()
            ->selectRaw("DATE_TRUNC('day', orders.created_at) AS day")
            ->selectRaw(
                "SUM(CASE WHEN orders.currency = ? THEN orders.total ELSE orders.total / COALESCE(NULLIF(curr.rate, 0), 1) END)::numeric(14, 2) AS revenue",
                [$baseCurrency],
            )
            ->leftJoin('currencies as curr', 'curr.code', '=', 'orders.currency')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', [OrderStatus::Paid->value, OrderStatus::Shipped->value])
            ->groupByRaw("DATE_TRUNC('day', orders.created_at)")
            ->orderBy('day')
            ->get()
            ->keyBy(fn ($row) => $row->day instanceof \DateTimeInterface ? $row->day->format('Y-m-d') : (string) $row->day);

        $labels = [];
        $data = [];

        for ($date = $start; $date <= $end; $date = $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $key;
            $data[] = isset($rows[$key]) ? (float) $rows[$key]->revenue : 0.0;
        }

        return [
            'labels' => $labels,
            'values' => $data,
        ];
    }

    public function getConversionSummary(DashboardPeriod $period): array
    {
        [$start, $end] = $period->range();

        $ordersCount = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', [OrderStatus::Paid->value, OrderStatus::Shipped->value])
            ->count();

        $cartsCount = Cart::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $conversionRate = $cartsCount > 0 ? round(($ordersCount / $cartsCount) * 100, 2) : 0.0;

        return [
            'orders' => $ordersCount,
            'carts' => $cartsCount,
            'rate' => $conversionRate,
        ];
    }

    public function getTrafficSources(DashboardPeriod $period): Collection
    {
        [$start, $end] = $period->range();
        $baseCurrency = $this->getBaseCurrency();

        $channelExpression = "COALESCE(NULLIF(TRIM(c.name), ''), 'Direct / Organic')";

        $rows = Order::query()
            ->selectRaw("{$channelExpression} AS channel")
            ->selectRaw(
                "COUNT(*) AS orders_count, SUM(CASE WHEN orders.currency = ? THEN orders.total ELSE orders.total / COALESCE(NULLIF(curr.rate, 0), 1) END)::numeric(14, 2) AS revenue",
                [$baseCurrency],
            )
            ->leftJoin('coupons as c', 'c.id', '=', 'orders.coupon_id')
            ->leftJoin('currencies as curr', 'curr.code', '=', 'orders.currency')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', [OrderStatus::Paid->value, OrderStatus::Shipped->value])
            ->groupByRaw($channelExpression)
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = max(1.0, $rows->sum(fn ($row) => (float) $row->revenue));

        return $rows->map(function ($row) use ($totalRevenue) {
            $revenue = (float) $row->revenue;

            return [
                'channel' => (string) $row->channel,
                'orders' => (int) $row->orders_count,
                'revenue' => $revenue,
                'share' => $revenue > 0 ? round(($revenue / $totalRevenue) * 100, 2) : 0.0,
            ];
        });
    }

    public function getInventoryStatus(): array
    {
        $stocks = ProductStock::query()
            ->selectRaw('product_id, SUM(qty) AS qty, SUM(reserved) AS reserved')
            ->groupBy('product_id')
            ->get();

        $totalSkus = $stocks->count();
        $availableUnits = $stocks->sum(fn ($row) => max(0, (int) $row->qty - (int) $row->reserved));
        $threshold = (int) config('shop.inventory.low_stock_threshold', 5);
        $lowStock = $stocks->filter(fn ($row) => max(0, (int) $row->qty - (int) $row->reserved) <= $threshold)->count();

        return [
            'skus' => $totalSkus,
            'available_units' => $availableUnits,
            'low_stock' => $lowStock,
            'threshold' => $threshold,
        ];
    }

    public function getMarketingPerformance(DashboardPeriod $period): array
    {
        return $this->marketingAnalytics->getCampaignPerformance($period);
    }

    public function topProductsQuery(): Builder
    {
        $baseCurrency = $this->getBaseCurrency();

        return OrderItem::query()
            ->select('order_items.product_id')
            ->selectRaw('MIN(order_items.id) AS id')
            ->selectRaw('products.name AS product_name')
            ->selectRaw('products.sku AS product_sku')
            ->selectRaw('SUM(order_items.qty)::integer AS total_qty')
            ->selectRaw(
                'SUM(CASE WHEN orders.currency = ? THEN order_items.qty * order_items.price ELSE (order_items.qty * order_items.price) / COALESCE(NULLIF(curr.rate, 0), 1) END)::numeric(14, 2) AS revenue_base',
                [$baseCurrency],
            )
            ->selectRaw('MAX(orders.created_at) AS last_order_at')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('currencies as curr', 'curr.code', '=', 'orders.currency')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->groupBy('order_items.product_id', 'products.name', 'products.sku');
    }

    public function applyPeriodConstraint(Builder|QueryBuilder $query, DashboardPeriod $period, string $column = 'created_at'): void
    {
        [$start, $end] = $period->range();

        $query->whereBetween($column, [$start, $end]);
    }
}
