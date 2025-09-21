<?php

namespace App\Filament\Mine\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use function formatCurrency;

class LoyaltyPointTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyPointTransactions';
    protected static ?string $recordTitleAttribute = 'description';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('shop.common.created'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('shop.loyalty.transactions.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('shop.loyalty.transactions.types.' . $state)),
                TextColumn::make('points')
                    ->label(__('shop.loyalty.transactions.fields.points'))
                    ->state(fn ($record) => (int) $record->points),
                TextColumn::make('amount')
                    ->label(__('shop.loyalty.transactions.fields.amount'))
                    ->state(fn ($record) => formatCurrency($record->amount, $record->order?->currency))
                    ->toggleable(),
                TextColumn::make('description')
                    ->formatStateUsing(fn ($state, $record) => $record->localized_description ?: $state)
                    ->wrap()
                    ->toggleable(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(mixed $record): bool
    {
        return false;
    }

    protected function canDelete(mixed $record): bool
    {
        return false;
    }
}
