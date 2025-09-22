<?php

namespace App\Filament\Mine\Widgets;

use App\Enums\Permission;
use App\Services\Analytics\DashboardMetricsService;
use App\Support\Dashboard\DashboardPeriod;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function formatCurrency;

class TopProductsTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = null;

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('shop.admin.dashboard.top_products.title');
    }

    public function table(Table $table): Table
    {
        $metrics = app(DashboardMetricsService::class);
        $baseCurrency = $metrics->getBaseCurrency();

        return $table
            ->query(fn () => $metrics->topProductsQuery())
            ->modifyQueryUsing(function (Builder $query) use ($metrics): void {
                $metrics->applyPeriodConstraint($query, $this->getActivePeriod(), 'orders.created_at');
            })
            ->columns([
                TextColumn::make('product_name')
                    ->label(__('shop.admin.dashboard.top_products.columns.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_sku')
                    ->label(__('shop.admin.dashboard.top_products.columns.sku'))
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('total_qty')
                    ->label(__('shop.admin.dashboard.top_products.columns.quantity'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((int) $state)),
                TextColumn::make('revenue_base')
                    ->label(__('shop.admin.dashboard.top_products.columns.revenue'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => formatCurrency((float) $state, $baseCurrency)),
            ])
            ->paginated(false)
            ->defaultSort('revenue_base', 'desc')
            ->filters([
                SelectFilter::make('period')
                    ->label(__('shop.admin.dashboard.filters.period'))
                    ->options(DashboardPeriod::options())
                    ->default(DashboardPeriod::ThirtyDays->value)
                    ->query(function (Builder $query, array $data) use ($metrics) {
                        $period = DashboardPeriod::tryFromFilter($data['value'] ?? null);

                        $metrics->applyPeriodConstraint($query, $period, 'orders.created_at');

                        return $query;
                    })
                    ->native(false),
            ]);
    }

    protected function getActivePeriod(): DashboardPeriod
    {
        $state = $this->getTableFilterState('period')['value'] ?? null;

        return DashboardPeriod::tryFromFilter($state);
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user?->can(Permission::ViewProducts->value) ?? false;
    }
}
