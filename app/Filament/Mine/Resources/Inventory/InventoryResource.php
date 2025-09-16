<?php

namespace App\Filament\Mine\Resources\Inventory;

use App\Filament\Mine\Resources\Inventory\Pages\CreateInventory;
use App\Filament\Mine\Resources\Inventory\Pages\EditInventory;
use App\Filament\Mine\Resources\Inventory\Pages\ListInventory;
use App\Models\ProductStock;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = ProductStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|null|\UnitEnum $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'id';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->native(false)
                ->required()
                ->disabledOn('edit'),
            Select::make('warehouse_id')
                ->relationship('warehouse', 'name')
                ->searchable()
                ->preload()
                ->native(false)
                ->required()
                ->disabledOn('edit'),
            TextInput::make('qty')
                ->label('Quantity')
                ->numeric()
                ->required()
                ->minValue(0),
            TextInput::make('reserved')
                ->numeric()
                ->required()
                ->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available')
                    ->label('Available')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventory::route('/'),
            'create' => CreateInventory::route('/create'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ProductStock::count();
    }
}
