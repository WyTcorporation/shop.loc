<?php

namespace App\Filament\Mine\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
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
                Select::make('status')
                    ->label('Status')
                    ->options([
                        OrderStatus::New->value     => 'New',
                        OrderStatus::Paid->value    => 'Paid',
                        OrderStatus::Shipped->value => 'Shipped',
                        OrderStatus::Cancelled->value=> 'Cancelled',
                    ])
                    ->default(OrderStatus::New->value)
                    ->required()
                    ->disabledOn('create'),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Fieldset::make('Shipping address')
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('city')->required(),
                        TextInput::make('addr')->label('Address')->required(),
                    ])
                    ->statePath('shipping_address')    // важливо!
                    ->columns(3),

                Fieldset::make('Billing address')
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('city'),
                        TextInput::make('addr')->label('Address'),
                    ])
                    ->statePath('billing_address')
                    ->columns(3),
                Textarea::make('note')->columnSpanFull(),
            ]);
    }
}
