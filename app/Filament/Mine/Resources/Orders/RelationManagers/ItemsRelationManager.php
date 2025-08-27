<?php

namespace App\Filament\Mine\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()->preload()->required()
                    ->disabled(fn ($livewire) => $livewire->ownerRecord->status !== OrderStatus::New),

                TextInput::make('qty')->numeric()->minValue(1)->required()
                    ->disabled(fn ($livewire) => $livewire->ownerRecord->status !== OrderStatus::New),

                TextInput::make('price')->numeric()->required()
                    ->disabled(fn ($livewire) => $livewire->ownerRecord->status !== OrderStatus::New),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('OrderItem')
            ->columns([
                TextColumn::make('product.name')->label('Product'),
                TextColumn::make('qty'),
                TextColumn::make('price')->money('usd'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(fn ($record) => number_format($record->qty * (float) $record->price, 2)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make()->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New),
                DetachAction::make(),
                DeleteAction::make()->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->emptyStateHeading('No items');
    }
}
