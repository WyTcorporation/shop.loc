<?php

namespace App\Filament\Mine\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Mine\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendOrderConfirmation;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label(__('shop.orders.fields.user'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('number')
                    ->label(__('shop.orders.fields.number'))
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('shop.orders.fields.total'))
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
//                TextColumn::make('status')
//                    ->label('Status')
//                    ->badge()
//                    ->getStateUsing(fn (Order $record) => $record->status instanceof OrderStatus ? $record->status->value : (string) $record->status)
//                    ->color(fn (Order $record) => match($record->status instanceof OrderStatus ? $record->status->value : $record->status) {
//                        'new' => 'warning',
//                        'paid' => 'success',
//                        'shipped' => 'info',
//                        'canceled' => 'danger',
//                        default => 'gray',
//                    })->searchable(),
                TextColumn::make('status')
                    ->label(__('shop.common.status'))
                    ->badge()
                    ->state(fn ($record) => $record->status instanceof OrderStatus
                        ? $record->status->value
                        : (string) $record->status)
                    ->formatStateUsing(fn (string $state) => __('shop.orders.statuses.' . $state))

                    ->color(fn ($record) => $record->status instanceof OrderStatus
                        ? $record->status->badgeColor()
                        : OrderStatus::from((string) $record->status)->badgeColor()),
                TextColumn::make('shipping_address.city')->label(__('shop.common.city'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn (OrderStatus $case) => [$case->value => __('shop.orders.statuses.' . $case->value)]
                    )->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('messages')
                    ->label(__('shop.orders.actions.messages'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->url(fn (Order $record) => OrderResource::getUrl('messages', ['record' => $record]))
                    ->visible(fn (Order $record) => auth()->user()?->can('view', $record)),
                Action::make('markPaid')
                    ->label(__('shop.orders.actions.mark_paid'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::New->value)
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->markPaid();
                        Notification::make()->title(__('shop.orders.notifications.marked_paid'))->success()->send();
                    }),
                Action::make('markShipped')
                    ->label(__('shop.orders.actions.mark_shipped'))
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::Paid->value)
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('tracking_number')
                            ->label(__('shop.common.tracking_number'))
                            ->default(fn (Order $record) => $record->shipment?->tracking_number)
                            ->maxLength(255),
                    ])
                    ->action(function (Order $record, array $data) {
                        $trackingNumber = trim((string) ($data['tracking_number'] ?? ''));
                        $trackingNumber = $trackingNumber === '' ? null : $trackingNumber;

                        if ($record->shipment || $trackingNumber !== null) {
                            $record->shipment()->updateOrCreate([], [
                                'tracking_number' => $trackingNumber,
                            ]);
                            $record->unsetRelation('shipment');
                            $record->load('shipment');
                        }

                        $record->markShipped();
                        Notification::make()->title(__('shop.orders.notifications.marked_shipped'))->success()->send();
                    }),
                Action::make('cancel')
                    ->label(__('shop.orders.actions.cancel'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Order $record) => in_array($record->status, [OrderStatus::New->value, OrderStatus::Paid->value], true))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->cancel();
                        Notification::make()->title(__('shop.orders.notifications.cancelled'))->success()->send();
                    }),
                Action::make('resend')
                    ->label(__('shop.orders.actions.resend_confirmation'))
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => filled($record->email))
                    ->action(function ($record) {
                        SendOrderConfirmation::dispatch($record, $record->locale);
                        \Filament\Notifications\Notification::make()
                            ->title(__('shop.orders.notifications.confirmation_resent'))
                            ->success()
                            ->send();
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
