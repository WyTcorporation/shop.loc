<?php

namespace App\Filament\Mine\Resources\Orders;

use App\Enums\Permission;
use App\Filament\Mine\Resources\Orders\Pages\CreateOrder;
use App\Filament\Mine\Resources\Orders\Pages\EditOrder;
use App\Filament\Mine\Resources\Orders\Pages\ListOrders;
use App\Filament\Mine\Resources\Orders\Pages\OrderMessages;
use App\Filament\Mine\Resources\Orders\Schemas\OrderForm;
use App\Filament\Mine\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'number';

//    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|null|\UnitEnum $navigationGroup = null;
    protected static bool $shouldRegisterNavigation = true;


    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', static::getModel()), 403);

        $query = parent::getEloquentQuery();

        if ($user?->vendor && ! $user->can(Permission::ManageVendors->value)) {
            $query->whereHas('items.product', fn ($builder) => $builder->where('vendor_id', $user->vendor->id));
        }

        return $query->with(['items.product.vendor', 'logs.user']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.orders.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.orders.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.sales');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
            'messages' => OrderMessages::route('/{record}/messages'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if (! $user?->can('viewAny', static::getModel())) {
            return null;
        }

        $query = Order::query();

        if ($user->vendor && ! $user->can(Permission::ManageVendors->value)) {
            $query->whereHas('items.product', fn ($builder) => $builder->where('vendor_id', $user->vendor->id));
        }

        return (string) $query->count();
    }
}
