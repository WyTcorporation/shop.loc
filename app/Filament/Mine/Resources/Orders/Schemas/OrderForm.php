<?php

namespace App\Filament\Mine\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;


class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')->schema([
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('email', optional(User::find($state))->email);
                        }),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required(fn (Get $get) => blank($get('user_id')))
                        ->helperText('Якщо вибрано користувача — поле підставиться автоматично.'),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            OrderStatus::New->value     => 'New',
                            OrderStatus::Paid->value    => 'Paid',
                            OrderStatus::Shipped->value => 'Shipped',
                            OrderStatus::Cancelled->value=> 'Cancelled',
                        ])
                        ->default(OrderStatus::New->value)
                        ->required(),

                    TextInput::make('number')
                        ->label('Number')
                        ->disabled()
                        ->dehydrated(false) // номер генерується у моделі
                        ->hint('Згенерується автоматично'),

                    TextInput::make('total')
                        ->label('Total')
                        ->prefix('₴')
                        ->disabled()
                        ->dehydrated(false)

                ])->columns(2),

                Section::make('Shipping')->schema([
                    Fieldset::make('Shipping address')->schema([
                        TextInput::make('shipping_address.name')->label('Name'),
                        TextInput::make('shipping_address.city')->label('City'),
                        TextInput::make('shipping_address.addr')->label('Address'),
                    ])->columns(3),

                    Fieldset::make('Billing address')->schema([
                        TextInput::make('billing_address.name')->label('Name'),
                        TextInput::make('billing_address.city')->label('City'),
                        TextInput::make('billing_address.addr')->label('Address'),
                    ])->columns(3),
                ])->columns(1),

                TextInput::make('note')->label('Note')->columnSpanFull(),
            ])->columns(2);
    }
}
