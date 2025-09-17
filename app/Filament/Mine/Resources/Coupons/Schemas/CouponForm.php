<?php

namespace App\Filament\Mine\Resources\Coupons\Schemas;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use function currencySymbol;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->helperText('Unique coupon code customers will enter.'),
            TextInput::make('name')
                ->maxLength(255),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            Select::make('type')
                ->required()
                ->options([
                    Coupon::TYPE_FIXED => 'Fixed amount',
                    Coupon::TYPE_PERCENT => 'Percentage',
                ])
                ->native(false),
            TextInput::make('value')
                ->required()
                ->numeric()
                ->prefix(fn ($state, callable $get) => $get('type') === Coupon::TYPE_PERCENT ? '%' : currencySymbol()),
            TextInput::make('min_cart_total')
                ->numeric()
                ->default(0)
                ->prefix(currencySymbol()),
            TextInput::make('max_discount')
                ->numeric()
                ->prefix(currencySymbol()),
            TextInput::make('usage_limit')
                ->numeric()
                ->minValue(0)
                ->label('Total usage limit'),
            TextInput::make('per_user_limit')
                ->numeric()
                ->minValue(0)
                ->label('Per user limit'),
            DateTimePicker::make('starts_at')
                ->seconds(false)
                ->label('Starts at'),
            DateTimePicker::make('expires_at')
                ->seconds(false)
                ->label('Expires at'),
            Toggle::make('is_active')
                ->default(true)
                ->label('Active'),
        ])->columns(2);
    }
}
