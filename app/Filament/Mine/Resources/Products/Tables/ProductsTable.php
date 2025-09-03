<?php

namespace App\Filament\Mine\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

//                TextColumn::make('slug')
//                    ->searchable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('category.name')->label('Category')->toggleable()->sortable(),

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
                TextInputColumn::make('stock')
                    ->label('Stock')
                    ->rules(['required', 'integer', 'min:0'])
                    ->sortable()
                    ->extraAttributes(['class' => 'text-right'])
                    ->width('100px'),

                TextInputColumn::make('price')
                    ->label('Price')
                    ->rules(['required', 'decimal:0,2', 'min:0'])
                    ->type('number')
                    ->step('0.01')
                    ->sortable()
                    ->width('120px')
                    ->extraAttributes(['class' => 'text-right']),

                ToggleColumn::make('is_active')
                    ->label('Active')
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
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
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
