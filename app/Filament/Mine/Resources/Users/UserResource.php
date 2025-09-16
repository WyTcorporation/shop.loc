<?php

namespace App\Filament\Mine\Resources\Users;

use App\Filament\Mine\Resources\Users\Pages\ListUsers;
use App\Filament\Mine\Resources\Users\Pages\ViewUser;
use App\Filament\Mine\Resources\Users\RelationManagers\LoyaltyPointTransactionsRelationManager;
use App\Filament\Mine\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static string|null|\UnitEnum $navigationGroup = 'Customers';
    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema;
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
            'view' => ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('loyaltyPointTransactions as loyalty_points_balance', 'points');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }
}
