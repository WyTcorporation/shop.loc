<?php

namespace App\Filament\Mine\Resources\Permissions;

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Mine\Resources\Permissions\Pages\EditPermission;
use App\Filament\Mine\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Mine\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Mine\Resources\Permissions\Tables\PermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionResource extends Resource
{
    protected static ?string $model = SpatiePermission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can(PermissionEnum::ManageUsers->value) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.settings');
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.permissions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.permissions.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::user()?->can('viewAny', SpatiePermission::class)) {
            return null;
        }

        return (string) SpatiePermission::count();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', SpatiePermission::class), 403);

        return parent::getEloquentQuery()
            ->with(['roles', 'users']);
    }
}
