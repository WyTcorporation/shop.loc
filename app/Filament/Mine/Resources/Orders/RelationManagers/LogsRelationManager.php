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
    protected static ?string $title = 'Status history';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_status')
            ->columns([
                TextColumn::make('from_status')->label('From')->badge(),
                TextColumn::make('to_status')->label('To')->badge(),
                TextColumn::make('user.name')->label('By')->placeholder('—'),
                TextColumn::make('note')->limit(60)->wrap(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No status changes yet')
            ->headerActions([])
            ->recordActions([]);
    }
}
