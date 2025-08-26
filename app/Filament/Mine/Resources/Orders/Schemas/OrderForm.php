<?php

namespace App\Filament\Mine\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_address')
                    ->required(),
                TextInput::make('billing_address'),
            ]);
    }
}
