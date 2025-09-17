<?php

namespace App\Filament\Mine\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use function currencySymbol;


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
                        ->dehydrated(false)
                        ->hint('Згенерується автоматично'),

                    TextInput::make('total')
                        ->label('Total')
                        ->prefix(fn (?Order $record) => currencySymbol($record?->currency))
                        ->disabled()
                        ->dehydrated(false)

                ])->columns(2),

                Section::make('Shipping')->schema([
                    Fieldset::make('Shipping address')->schema([
                        TextInput::make('shipping_address.name')->label('Name'),
                        TextInput::make('shipping_address.city')->label('City'),
                        TextInput::make('shipping_address.addr')->label('Address'),
                        TextInput::make('shipping_address.postal_code')->label('Postal code'),
                        TextInput::make('shipping_address.phone')->label('Phone')->tel(),
                    ])->columns(2),

                    Fieldset::make('Billing address')->schema([
                        TextInput::make('billing_address.name')->label('Name'),
                        TextInput::make('billing_address.city')->label('City'),
                        TextInput::make('billing_address.addr')->label('Address'),
                        TextInput::make('billing_address.postal_code')->label('Postal code'),
                        TextInput::make('billing_address.phone')->label('Phone')->tel(),
                    ])->columns(2),
                ])->columns(1),

                TextInput::make('note')->label('Note')->columnSpanFull(),

                Section::make('Shipment')->schema([
                    TextInput::make('shipment_tracking_number')
                        ->label('Tracking number')
                        ->maxLength(255)
                        ->afterStateHydrated(function (TextInput $component, ?Order $record) {
                            $component->state($record?->shipment?->tracking_number);
                        })
                        ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state),

                    Select::make('shipment_status')
                        ->label('Shipment status')
                        ->options(collect(ShipmentStatus::cases())->mapWithKeys(fn (ShipmentStatus $case) => [$case->value => $case->label()])->all())
                        ->default(ShipmentStatus::Pending->value)
                        ->native(false)
                        ->afterStateHydrated(function (Select $component, ?Order $record) {
                            $status = $record?->shipment?->status;
                            $component->state($status instanceof ShipmentStatus ? $status->value : ($status ?: ShipmentStatus::Pending->value));
                        })
                        ->dehydrateStateUsing(fn ($state) => $state ?? ShipmentStatus::Pending->value),
                ])->columns(2),

                Section::make('Summary')
                    ->collapsible()
                    ->schema([
                        ViewField::make('orderSummary')
                            ->view('filament.orders.summary')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
