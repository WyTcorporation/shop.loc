<?php

namespace App\Filament\Mine\Resources\Categories;

use App\Filament\Mine\Resources\Categories\Pages\CreateCategory;
use App\Filament\Mine\Resources\Categories\Pages\EditCategory;
use App\Filament\Mine\Resources\Categories\Pages\ListCategories;
use App\Filament\Mine\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Mine\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|null|\UnitEnum $navigationGroup = null;
    protected static bool $shouldRegisterNavigation = true;


    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Category::count();
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.categories.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.categories.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.catalog');
    }
}
