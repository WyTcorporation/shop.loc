<?php

namespace App\Filament\Mine\Resources\Users;

use App\Filament\Mine\Resources\Users\Pages\CreateUser;
use App\Filament\Mine\Resources\Users\Pages\EditUser;
use App\Filament\Mine\Resources\Users\Pages\ListUsers;
use App\Filament\Mine\Resources\Users\Pages\ViewUser;
use App\Filament\Mine\Resources\Users\RelationManagers\LoyaltyPointTransactionsRelationManager;
use App\Filament\Mine\Resources\Users\Schemas\UserForm;
use App\Filament\Mine\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static string|null|\UnitEnum $navigationGroup = null;
    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LoyaltyPointTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('viewAny', static::getModel()) ?? false;
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.users.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.users.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.customers');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        abort_if($user === null || ! $user->can('viewAny', static::getModel()), 403);

        return parent::getEloquentQuery()
            ->withSum('loyaltyPointTransactions as loyalty_points_balance', 'points');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::user()?->can('viewAny', static::getModel())) {
            return null;
        }

        return (string) User::count();
    }
}
