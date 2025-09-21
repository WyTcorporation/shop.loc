<?php

namespace App\Filament\Mine\Resources\Tests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('campaign.name')
                    ->label(__('Campaign'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('traffic_split_a')
                    ->label(__('Variant A %'))
                    ->sortable(),
                TextColumn::make('traffic_split_b')
                    ->label(__('Variant B %'))
                    ->sortable(),
                TextColumn::make('metrics.opens')
                    ->label(__('Opens'))
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('metrics.clicks')
                    ->label(__('Clicks'))
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('metrics.conversions')
                    ->label(__('Conversions'))
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
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
