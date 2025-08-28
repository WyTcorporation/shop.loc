<?php

namespace App\Filament\Mine\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Mine\Resources\Orders\Pages\EditOrder;

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
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $price = Product::find($state)?->price ?? 0;
                        $set('price', $price);
                    })
                    ->required(),
                TextInput::make('qty')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->reactive(),

                TextInput::make('price')
                    ->numeric()
                    ->rule('decimal:0,2')
                    ->required()
                    ->prefix('₴'),
            ])->columns(3);
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
                CreateAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
                AttachAction::make()->after(function (RelationManager $livewire) {
                    $order = $livewire->getOwnerRecord();
                    $order->recalculateTotal();
                    $livewire->dispatch('order-items-updated')
                        ->to(EditOrder::class);
                }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
                DeleteAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
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
