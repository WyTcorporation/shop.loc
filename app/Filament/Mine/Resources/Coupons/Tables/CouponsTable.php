<?php

namespace App\Filament\Mine\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use function formatCurrency;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label(__('shop.coupons.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('shop.coupons.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('shop.coupons.types.' . $state))
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('shop.coupons.fields.value'))
                    ->state(function (Coupon $record) {
                        return $record->type === Coupon::TYPE_PERCENT
                            ? number_format((float) $record->value, 2) . '%'
                            : formatCurrency($record->value);
                    }),
                TextColumn::make('min_cart_total')
                    ->label(__('shop.coupons.fields.min_cart'))
                    ->state(fn (Coupon $record) => formatCurrency($record->min_cart_total))
                    ->toggleable(),
                TextColumn::make('usage_limit')
                    ->label(__('shop.coupons.fields.usage'))
                    ->state(fn (Coupon $record) => $record->usage_limit
                        ? sprintf('%d / %d', $record->used, $record->usage_limit)
                        : (string) $record->used)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('shop.coupons.fields.is_active'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('shop.coupons.fields.starts_at'))
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label(__('shop.coupons.fields.expires_at'))
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('shop.common.updated'))
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('shop.coupons.filters.is_active'))
                    ->queries(
                        true: fn ($query) => $query->where('is_active', true),
                        false: fn ($query) => $query->where('is_active', false),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
