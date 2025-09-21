<?php

namespace App\Filament\Mine\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = __('shop.orders.logs.title');

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_status')
            ->columns([
                TextColumn::make('from_status')->label(__('shop.orders.logs.fields.from'))->badge(),
                TextColumn::make('to_status')->label(__('shop.orders.logs.fields.to'))->badge(),
                TextColumn::make('user.name')->label(__('shop.orders.logs.fields.by'))->placeholder('—'),
                TextColumn::make('note')->limit(60)->wrap(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('shop.orders.logs.empty_state.heading'))
            ->headerActions([])
            ->recordActions([]);
    }
}
