<?php

namespace App\Filament\Mine\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                    ->color(fn (string $s) => match ($s) {
                        'new' => 'warning',
                        'paid' => 'success',
                        'shipped' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }) ->searchable(),
                TextColumn::make('shipping_address.city')->label('City')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('mark_paid')
                    ->label('Mark paid')
                    ->visible(fn($record) => !$record->isCancelled() && is_null($record->paid_at))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            if ($record->isCancelled() || $record->paid_at) return;
                            $record->status = 'paid';
                            $record->paid_at = now();
                            $record->save();
                        });
                    }),

                Action::make('mark_shipped')
                    ->label('Mark shipped')
                    ->visible(fn($r)=> !$r->isCancelled() && !$r->isShipped())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            if ($record->isCancelled() || $record->isShipped()) return;
                            $record->status = 'shipped';
                            $record->shipped_at = now();
                            $record->save();
                        });
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->visible(fn($r)=> !$r->isCancelled() && !$r->isShipped())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            if ($record->isCancelled() || $record->isShipped()) return;
                            if ($record->inventoryCommitted()) {
                                foreach ($record->items as $it) {
                                    $it->product()->lockForUpdate()->first()?->increment('stock', (int)$it->qty);
                                }
                                $record->inventory_committed_at = null;
                            }

                            $record->status = 'cancelled';
                            $record->cancelled_at = now();
                            $record->save();
                        });
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
