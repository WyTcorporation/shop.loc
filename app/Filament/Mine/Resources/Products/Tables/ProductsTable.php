<?php

namespace App\Filament\Mine\Resources\Products\Tables;

use App\Filament\Mine\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function formatCurrency;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProductResource::getScopedQueryFor()
                ->with(['category', 'stocks']))
            ->columns([
                ImageColumn::make('preview')
                    ->label(__('shop.products.fields.preview'))
                    // важливо: саме getStateUsing для ImageColumn
                    ->getStateUsing(fn (Product $record) => $record->preview_url)
                    ->circular()
                    ->defaultImageUrl(asset('images/no-image.svg')),

                // (за бажанням) дебаг-колонка з URL
                TextColumn::make('preview_url_debug')
                    ->label(__('shop.products.fields.preview_url_debug'))
                    ->getStateUsing(fn (Product $r) => $r->preview_url ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
//                \Filament\Tables\Columns\ImageColumn::make('preview')
//                    ->label('Preview')
//                    ->getStateUsing(fn ($record) => $record->preview_url) // ← важливо
//                    ->circular()
//                    ->defaultImageUrl(asset('images/placeholder.png')),
//
//                // якщо хочеш дебаг-колонку:
//                \Filament\Tables\Columns\TextColumn::make('preview_url_debug')
//                    ->label('url?')
//                    ->formatStateUsing(fn ($state, $record) => $record->preview_url ?? '—')
//                    ->toggleable(isToggledHiddenByDefault: true),
//                ImageColumn::make('preview_path')
//                    ->label('Preview')
//                    ->getStateUsing(fn ($r) => $r->images
//                        ->sortBy([['is_primary','desc'],['sort','asc'],['id','asc']])
//                        ->first()?->path
//                    )
//                    ->disk('public')                 // тепер колонка сама складе URL
//                    ->circular()
//                    ->defaultImageUrl(asset('images/no-image.svg')),
//                ImageColumn::make('preview')
//                    ->label('Preview')
//                    ->getStateUsing(function (Product $record) {
//                        $img = $record->images->sortByDesc('is_primary')->sortBy('sort')->first();
//                        return $img ? \Storage::disk($img->disk ?? 'public')->url($img->path) : null;
//                    })
//                    ->circular(),
//                TextColumn::make('preview_url_debug')
//                    ->label('url?')
//                    ->state(fn ($record) => $record->preview_url ?? '—')
//                    ->toggleable(isToggledHiddenByDefault: true),
//                ImageColumn::make('preview_url')
//                    ->label('Preview')
//                    ->state(fn ($record) => $record->preview_url)
//                    ->circular(),
//                ImageColumn::make('preview')
//                    ->label('Preview')
//                    ->getStateUsing(fn ($record) => $record->preview_url ?: asset('images/no-image.svg'))
//                    ->circular(),
                TextColumn::make('name')
                    ->label(__('shop.products.fields.name'))
                    ->searchable(),

//                TextColumn::make('slug')
//                    ->searchable(),

                TextColumn::make('sku')
                    ->label(__('shop.products.fields.sku'))
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('shop.products.fields.category'))
                    ->toggleable()
                    ->sortable(),

//                TextColumn::make('attributes')
//                    ->label('Attrs')
//                    ->formatStateUsing(function ($state) {
//                        if (blank($state)) return '—';
//                        if (is_array($state) && array_is_list($state)) {
//                            return collect($state)
//                                ->map(fn ($row) => ($row['name'] ?? '?').':'.($row['value'] ?? ''))
//                                ->take(3)->join(', ');
//                        }
//                        return collect($state)->map(fn ($v, $k) => "$k:$v")->take(3)->join(', ');
//                    })
//                    ->tooltip(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state)
//                    ->wrap(),
                TextColumn::make('vendor.name')
                    ->label(__('shop.products.fields.vendor'))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy(
                            Vendor::query()
                                ->select('name')
                                ->whereColumn('vendors.id', Product::query()->qualifyColumn('vendor_id')),
                            $direction,
                        ))
                    ->searchable()
                    ->visible(fn () => Auth::user()?->vendor === null),

                TextColumn::make('stock')
                    ->label(__('shop.products.fields.stock'))
                    ->state(fn (Product $record): int => $record->relationLoaded('stocks')
                        ? (int) $record->stocks->sum('available')
                        : (int) $record->stock)
                    ->numeric()
                    ->sortable()
                    ->extraAttributes(['class' => 'text-right tabular-nums font-mono']),

                TextColumn::make('price')
                    ->label(__('shop.products.fields.price'))
                    ->formatStateUsing(fn ($state): string => formatCurrency($state))
                    ->sortable()
                    ->extraAttributes(['class' => 'text-right tabular-nums font-mono']),

                ToggleColumn::make('is_active')
                    ->label(__('shop.products.fields.is_active'))
                    ->sortable(),



//                TextColumn::make('price_old')
//                    ->numeric()
//                    ->sortable(),
//                IconColumn::make('is_active')
//                    ->boolean(),
//                TextColumn::make('deleted_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                TextColumn::make('updated_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('shop.products.filters.category'))
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label(__('shop.products.filters.is_active.label'))
                    ->trueLabel(__('shop.products.filters.is_active.true'))
                    ->falseLabel(__('shop.products.filters.is_active.false'))
                    ->queries(
                        true: fn ($q) => $q->where('is_active', true),
                        false: fn ($q) => $q->where('is_active', false),
                        blank: fn ($q) => $q,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
