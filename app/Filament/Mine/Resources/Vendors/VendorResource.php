<?php

namespace App\Filament\Mine\Resources\Vendors;

use App\Filament\Mine\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Mine\Resources\Vendors\Pages\EditVendor;
use App\Filament\Mine\Resources\Vendors\Pages\ListVendors;
use App\Filament\Mine\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Mine\Resources\Vendors\Tables\VendorsTable;
use App\Models\Vendor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', static::getModel()), 403);

        $query = parent::getEloquentQuery();

        if ($user?->vendor) {
            $query->whereKey($user->vendor->id);
        }

        return $query->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.vendors.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.vendors.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.catalog');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', static::getModel())) {
            return null;
        }

        return (string) Vendor::count();
    }
}
