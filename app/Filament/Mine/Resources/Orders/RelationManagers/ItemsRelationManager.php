<?php

namespace App\Filament\Mine\Resources\Orders\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('OrderItem')
                    ->required()
                    ->maxLength(255),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('OrderItem')
            ->columns([
//                TextColumn::make('OrderItem')
//                    ->searchable(),
                TextColumn::make('product.name')->label('Product')->limit(40),
                TextColumn::make('qty')->numeric()->label('Qty'),
                TextColumn::make('price')->money('USD', true),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(fn($r)=> (float)$r->qty * (float)$r->price)
                    ->money('USD', true),
                TextColumn::make('created_at')->since(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated(false)
            ->emptyStateHeading('No items');
    }
}
