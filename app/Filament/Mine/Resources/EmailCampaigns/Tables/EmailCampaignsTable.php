<?php

namespace App\Filament\Mine\Resources\EmailCampaigns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('segments_count')
                    ->label(__('Segments'))
                    ->sortable(),
                TextColumn::make('scheduled_for')
                    ->label(__('Scheduled for'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('open_count')
                    ->label(__('Opens'))
                    ->sortable(),
                TextColumn::make('click_count')
                    ->label(__('Clicks'))
                    ->sortable(),
                TextColumn::make('conversion_count')
                    ->label(__('Conversions'))
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label(__('Conversion rate'))
                    ->formatStateUsing(fn ($state, $record) => number_format($record->conversionRate(), 2) . '%')
                    ->sortable(),
                TextColumn::make('last_dispatched_at')
                    ->label(__('Last dispatched at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'scheduled' => __('Scheduled'),
                        'running' => __('Running'),
                        'completed' => __('Completed'),
                    ]),
            ])
            ->recordActions([
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
