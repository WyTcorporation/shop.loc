<?php

namespace App\Filament\Mine\Resources\Inventory;

use App\Filament\Mine\Resources\Inventory\Pages\CreateInventory;
use App\Filament\Mine\Resources\Inventory\Pages\EditInventory;
use App\Filament\Mine\Resources\Inventory\Pages\ListInventory;
use App\Models\ProductStock;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InventoryResource extends Resource
{
    protected static ?string $model = ProductStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|null|\UnitEnum $navigationGroup = null;

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
                ->live(onBlur: true)
                ->rule(function (callable $get, ?Model $record) {
                    $warehouseId = $get('warehouse_id');

                    if (! $warehouseId) {
                        return null;
                    }

                    $rule = Rule::unique('product_stocks', 'product_id')
                        ->where(fn ($query) => $query->where('warehouse_id', $warehouseId));

                    if ($record) {
                        $rule->ignore($record->getKey());
                    }

                    return $rule;
                })
                ->disabledOn('edit'),
            Select::make('warehouse_id')
                ->relationship('warehouse', 'name')
                ->searchable()
                ->preload()
                ->native(false)
                ->required()
                ->live(onBlur: true)
                ->rule(function (callable $get, ?Model $record) {
                    $productId = $get('product_id');

                    if (! $productId) {
                        return null;
                    }

                    $rule = Rule::unique('product_stocks', 'warehouse_id')
                        ->where(fn ($query) => $query->where('product_id', $productId));

                    if ($record) {
                        $rule->ignore($record->getKey());
                    }

                    return $rule;
                })
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.inventory.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.inventory.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.inventory');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ProductStock::count();
    }
}
