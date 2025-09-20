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
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->helperText('Unique coupon code customers will enter.'),
            TextInput::make('name')
                ->maxLength(255)
                ->hidden()
                ->afterStateHydrated(function (TextInput $component, $state, Set $set) use ($primaryLocale): void {
                    if (filled($state)) {
                        $set("name_translations.{$primaryLocale}", $state);
                    }
                })
                ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull()
                ->hidden()
                ->afterStateHydrated(function (Textarea $component, $state, Set $set) use ($primaryLocale): void {
                    if (filled($state)) {
                        $set("description_translations.{$primaryLocale}", $state);
                    }
                })
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
