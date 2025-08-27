<?php

namespace App\Filament\Mine\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('number')
                    ->searchable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        OrderStatus::New->value     => 'warning',
                        OrderStatus::Paid->value    => 'success',
                        OrderStatus::Shipped->value => 'info',
                        OrderStatus::Cancelled->value=> 'danger',
                        default => 'gray',
                    })->searchable(),
                TextColumn::make('shipping_address.city')->label('City')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->all()),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Mark paid')->icon('heroicon-o-banknotes')
                    ->visible(fn ($record) => $record->status === OrderStatus::New)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->transitionTo(OrderStatus::Paid)),

                Action::make('markShipped')
                    ->label('Mark shipped')->icon('heroicon-o-truck')
                    ->visible(fn ($record) => $record->status === OrderStatus::Paid)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->transitionTo(OrderStatus::Shipped)),

                Action::make('cancel')
                    ->label('Cancel')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [OrderStatus::New, OrderStatus::Paid], true))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->transitionTo(OrderStatus::Cancelled)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
