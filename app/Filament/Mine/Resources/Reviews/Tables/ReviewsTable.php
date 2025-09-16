<?php

namespace App\Filament\Mine\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
                    ->label('Product')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (?string $state) => $state ?? '—'),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (Review $record) => match ($record->status) {
                        Review::STATUS_APPROVED => 'success',
                        Review::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Review::STATUS_PENDING => 'Pending',
                        Review::STATUS_APPROVED => 'Approved',
                        Review::STATUS_REJECTED => 'Rejected',
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
