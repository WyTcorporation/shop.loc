<?php

namespace App\Filament\Mine\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('shop.common.name'))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Select::make('permissions')
                ->label(__('shop.users.fields.permissions'))
                ->relationship('permissions', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false),
            Select::make('users')
                ->label(__('shop.admin.resources.users.plural_label'))
                ->relationship('users', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->helperText(__('shop.admin.resources.roles.form.assign_users_help')),
        ]);
    }
}
