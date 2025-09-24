<?php

namespace App\Filament\Mine\Resources\Products;

use App\Enums\Permission;
use App\Filament\Mine\Resources\Products\Pages\CreateProduct;
use App\Filament\Mine\Resources\Products\Pages\EditProduct;
use App\Filament\Mine\Resources\Products\Pages\ExportProducts;
use App\Filament\Mine\Resources\Products\Pages\ImportProducts;
use App\Filament\Mine\Resources\Products\Pages\ListProducts;
use App\Filament\Mine\Resources\Products\Schemas\ProductForm;
use App\Filament\Mine\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

//    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static string|null|\UnitEnum $navigationGroup = null;
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
        $user = Auth::user();

        abort_if($user === null, 403);

        if (! $user->can(Permission::ViewProducts->value) && ! $user->can(Permission::ManageProducts->value)) {
            abort(403);
        }

        $query = parent::getEloquentQuery()
            ->with(['images' => fn($q) => $q
                ->select('id','product_id','path','disk','is_primary','sort')
                ->orderByDesc('is_primary')
                ->orderBy('sort')
            ]);

        $permittedCategoryIds = $user->permittedCategoryIds();

        if ($permittedCategoryIds->isNotEmpty()) {
            $query->whereIn('category_id', $permittedCategoryIds);
        }

        if ($user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
            'import' => ImportProducts::route('/import'),
            'export' => ExportProducts::route('/export'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.products.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.products.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.catalog');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', static::getModel())) {
            return null;
        }

        return (string) static::getEloquentQuery()->count();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
        ];
    }
}
