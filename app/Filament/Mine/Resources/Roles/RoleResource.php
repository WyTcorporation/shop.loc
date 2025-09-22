<?php

namespace App\Filament\Mine\Resources\Roles;

use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Roles\Pages\CreateRole;
use App\Filament\Mine\Resources\Roles\Pages\EditRole;
use App\Filament\Mine\Resources\Roles\Pages\ListRoles;
use App\Filament\Mine\Resources\Roles\Schemas\RoleForm;
use App\Filament\Mine\Resources\Roles\Tables\RolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleResource extends Resource
{
    protected static ?string $model = SpatieRole::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
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
        return __('shop.admin.resources.roles.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.roles.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::user()?->can('viewAny', SpatieRole::class)) {
            return null;
        }

        return (string) SpatieRole::count();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', SpatieRole::class), 403);

        return parent::getEloquentQuery()
            ->with(['permissions', 'users']);
    }
}
