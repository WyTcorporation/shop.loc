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
                Section::make(__('shop.orders.sections.general'))->schema([
                    Select::make('user_id')
                        ->label(__('shop.orders.fields.user'))
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('email', optional(User::find($state))->email);
                        }),

                    TextInput::make('email')
                        ->label(__('shop.common.email'))
                        ->email()
                        ->required(fn (Get $get) => blank($get('user_id')))
                        ->helperText(__('shop.orders.helpers.email_auto')),

                    Select::make('status')
                        ->label(__('shop.common.status'))
                        ->options(collect(OrderStatus::cases())->mapWithKeys(
                            fn (OrderStatus $case) => [$case->value => __('shop.orders.statuses.' . $case->value)]
                        )->all())
                        ->default(OrderStatus::New->value)
                        ->required(),

                    TextInput::make('number')
                        ->label(__('shop.orders.fields.number'))
                        ->disabled()
                        ->dehydrated(false)
                        ->hint(__('shop.orders.hints.number_generated')),

                    TextInput::make('total')
                        ->label(__('shop.common.total'))
                        ->prefix(fn (?Order $record) => currencySymbol($record?->currency))
                        ->disabled()
                        ->dehydrated(false)

                ])->columns(2),

                Section::make(__('shop.orders.sections.shipping'))->schema([
                    Fieldset::make(__('shop.orders.fieldsets.shipping_address'))->schema([
                        TextInput::make('shipping_address.name')->label(__('shop.common.name')),
                        TextInput::make('shipping_address.city')->label(__('shop.common.city')),
                        TextInput::make('shipping_address.addr')->label(__('shop.common.address')),
                        TextInput::make('shipping_address.postal_code')->label(__('shop.common.postal_code')),
                        TextInput::make('shipping_address.phone')->label(__('shop.common.phone'))->tel(),
                    ])->columns(2),

                    Fieldset::make(__('shop.orders.fieldsets.billing_address'))->schema([
                        TextInput::make('billing_address.name')->label(__('shop.common.name')),
                        TextInput::make('billing_address.city')->label(__('shop.common.city')),
                        TextInput::make('billing_address.addr')->label(__('shop.common.address')),
                        TextInput::make('billing_address.postal_code')->label(__('shop.common.postal_code')),
                        TextInput::make('billing_address.phone')->label(__('shop.common.phone'))->tel(),
                    ])->columns(2),
                ])->columns(1),

                Textarea::make('note')
                    ->label(__('shop.common.note'))
                    ->rows(4)
                    ->columnSpanFull(),

                Section::make(__('shop.orders.sections.shipment'))->schema([
                    TextInput::make('shipment_tracking_number')
                        ->label(__('shop.common.tracking_number'))
                        ->maxLength(255)
                        ->afterStateHydrated(function (TextInput $component, ?Order $record) {
                            $component->state($record?->shipment?->tracking_number);
                        })
                        ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state),

                    TextInput::make('shipment_delivery_method')
                        ->label(__('shop.common.delivery_method'))
                        ->maxLength(255)
                        ->afterStateHydrated(function (TextInput $component, ?Order $record) {
                            $component->state($record?->shipment?->delivery_method);
                        })
                        ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state),

                    Select::make('shipment_status')
                        ->label(__('shop.orders.fields.shipment_status'))
                        ->options(collect(ShipmentStatus::cases())->mapWithKeys(fn (ShipmentStatus $case) => [$case->value => $case->label()])->all())
                        ->default(ShipmentStatus::Pending->value)
                        ->native(false)
                        ->afterStateHydrated(function (Select $component, ?Order $record) {
                            $status = $record?->shipment?->status;
                            $component->state($status instanceof ShipmentStatus ? $status->value : ($status ?: ShipmentStatus::Pending->value));
                        })
                        ->dehydrateStateUsing(fn ($state) => $state ?? ShipmentStatus::Pending->value),
                ])->columns(2),

                Section::make(__('shop.orders.sections.summary'))
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
