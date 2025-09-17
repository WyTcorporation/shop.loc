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
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('points')
                    ->label('Points')
                    ->state(fn ($record) => (int) $record->points),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->state(fn ($record) => formatCurrency($record->amount, $record->order?->currency))
                    ->toggleable(),
                TextColumn::make('description')
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
