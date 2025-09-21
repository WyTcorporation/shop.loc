<?php

namespace App\Filament\Mine\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('shop.common.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label(__('shop.common.email'))
                ->email()
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label(__('shop.users.fields.password'))
                ->password()
                ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? $state : null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->maxLength(255),
            Select::make('roles')
                ->label(__('shop.users.fields.roles'))
                ->relationship('roles', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false),
            Select::make('categories')
                ->label(__('shop.users.fields.categories'))
                ->relationship('categories', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)
                ->columnSpanFull(),
        ]);
    }
}
