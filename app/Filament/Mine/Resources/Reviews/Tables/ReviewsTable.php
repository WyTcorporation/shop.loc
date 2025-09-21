<?php

namespace App\Filament\Mine\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Review::query()->with(['product', 'user']))
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('shop.reviews.fields.product'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label(__('shop.reviews.fields.user'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (?string $state) => $state ?? '—'),

                TextColumn::make('rating')
                    ->label(__('shop.reviews.fields.rating'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('shop.reviews.fields.status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => __('shop.reviews.statuses.' . $state))
                    ->color(fn (Review $record) => match ($record->status) {
                        Review::STATUS_APPROVED => 'success',
                        Review::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label(__('shop.reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('shop.reviews.filters.status'))
                    ->options([
                        Review::STATUS_PENDING => __('shop.reviews.statuses.pending'),
                        Review::STATUS_APPROVED => __('shop.reviews.statuses.approved'),
                        Review::STATUS_REJECTED => __('shop.reviews.statuses.rejected'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
