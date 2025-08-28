<?php

namespace App\Filament\Mine\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Order $record) => $record->status instanceof OrderStatus ? $record->status->value : (string) $record->status)
                    ->color(fn (Order $record) => match($record->status instanceof OrderStatus ? $record->status->value : $record->status) {
                        'new' => 'warning',
                        'paid' => 'success',
                        'shipped' => 'info',
                        'canceled' => 'danger',
                        default => 'gray',
                    })->searchable(),
                TextColumn::make('shipping_address.city')->label('City')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::New->value)
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->markPaid();
                        Notification::make()->title('Order marked as paid')->success()->send();
                    }),
                Action::make('markShipped')
                    ->label('Mark shipped')
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::Paid->value)
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->markShipped();
                        Notification::make()->title('Order marked as shipped')->success()->send();
                    }),
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Order $record) => in_array($record->status, [OrderStatus::New->value, OrderStatus::Paid->value], true))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->cancel();
                        Notification::make()->title('Order canceled')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
