<?php

namespace App\Filament\Mine\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('shop.common.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('shop.common.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('loyalty_points_balance')
                    ->label(__('shop.users.fields.points_balance'))
                    ->state(fn (User $record) => (int) ($record->loyalty_points_balance ?? 0))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('shop.common.created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
