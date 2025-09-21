<?php

namespace App\Filament\Mine\Resources\Coupons\Schemas;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use function currencySymbol;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->values();

        return $schema->components([
            TextInput::make('code')
                ->label(__('shop.coupons.fields.code'))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->helperText(__('shop.coupons.helpers.code_unique')),
            TextInput::make('name')
                ->label(__('shop.coupons.fields.name'))
                ->maxLength(255)
                ->hidden()
                ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
            Textarea::make('description')
                ->label(__('shop.coupons.fields.description'))
                ->rows(3)
                ->columnSpanFull()
                ->hidden()
                ->dehydrateStateUsing(fn ($state, Get $get) => $get('description_translations.' . $primaryLocale) ?? $state),
            Tabs::make('translations')
                ->columnSpanFull()
                ->tabs(
                    $supportedLocales
                        ->map(fn (string $locale): Tab => Tab::make(strtoupper($locale))
                            ->schema([
                                TextInput::make("name_translations.{$locale}")
                                    ->label(__('shop.common.name'))
                                    ->required($locale === $primaryLocale)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                        if ($locale === $primaryLocale) {
                                            $set('name', $state);
                                        }
                                    }),
                                Textarea::make("description_translations.{$locale}")
                                    ->label(__('shop.products.fields.description'))
                                    ->columnSpanFull()
                                    ->live(onBlur: true)
                                    ->required($locale === $primaryLocale)
                                    ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                        if ($locale === $primaryLocale) {
                                            $set('description', $state);
                                        }
                                    }),
                            ]))
                        ->toArray(),
                ),
            Select::make('type')
                ->label(__('shop.coupons.fields.type'))
                ->required()
                ->options([
                    Coupon::TYPE_FIXED => __('shop.coupons.types.fixed'),
                    Coupon::TYPE_PERCENT => __('shop.coupons.types.percent'),
                ])
                ->native(false),
            TextInput::make('value')
                ->label(__('shop.coupons.fields.value'))
                ->required()
                ->numeric()
                ->prefix(fn ($state, callable $get) => $get('type') === Coupon::TYPE_PERCENT ? '%' : currencySymbol()),
            TextInput::make('min_cart_total')
                ->label(__('shop.coupons.fields.min_cart'))
                ->numeric()
                ->default(0)
                ->prefix(currencySymbol()),
            TextInput::make('max_discount')
                ->label(__('shop.coupons.fields.max_discount'))
                ->numeric()
                ->prefix(currencySymbol()),
            TextInput::make('usage_limit')
                ->label(__('shop.coupons.fields.usage_limit'))
                ->numeric()
                ->minValue(0),
            TextInput::make('per_user_limit')
                ->label(__('shop.coupons.fields.per_user_limit'))
                ->numeric()
                ->minValue(0),
            DateTimePicker::make('starts_at')
                ->seconds(false)
                ->label(__('shop.coupons.fields.starts_at')),
            DateTimePicker::make('expires_at')
                ->seconds(false)
                ->label(__('shop.coupons.fields.expires_at')),
            Toggle::make('is_active')
                ->default(true)
                ->label(__('shop.coupons.fields.is_active')),
        ])->columns(2);
    }
}
