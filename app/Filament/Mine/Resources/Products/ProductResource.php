<?php

namespace App\Filament\Mine\Resources\Products;

use App\Filament\Mine\Resources\Products\Pages\CreateProduct;
use App\Filament\Mine\Resources\Products\Pages\EditProduct;
use App\Filament\Mine\Resources\Products\Pages\ListProducts;
use App\Filament\Mine\Resources\Products\Schemas\ProductForm;
use App\Filament\Mine\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Product';

//    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static string|null|\UnitEnum $navigationGroup = 'Catalog';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['images' => fn($q) => $q
                ->select('id','product_id','path','disk','is_primary','sort')
                ->orderByDesc('is_primary')
                ->orderBy('sort')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Product::count();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
        ];
    }
}
