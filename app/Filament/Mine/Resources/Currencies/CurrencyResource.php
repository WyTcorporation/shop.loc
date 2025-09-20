<?php

namespace App\Filament\Mine\Resources\Currencies;

use App\Filament\Mine\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Mine\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Mine\Resources\Currencies\Pages\ListCurrencies;
use App\Models\Currency;
use App\Services\Currency\CurrencyConverter;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'code';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->minLength(3)
                ->maxLength(3)
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('code', strtoupper((string) $state)))
                ->rule('alpha:ascii'),
            TextInput::make('rate')
                ->label(__('shop.currencies.rate_vs_base'))
                ->numeric()
                ->required()
                ->minValue(0.00000001)
                ->step('0.00000001'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('shop.currencies.code'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => Str::upper($state)),
                TextColumn::make('rate')
                    ->label(__('shop.currencies.rate'))
                    ->numeric(decimalPlaces: 8)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('shop.currencies.updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.currencies.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        $base = config('shop.currency.base');

        if (is_string($base) && $base !== '') {
            return strtoupper($base);
        }

        return app(CurrencyConverter::class)->getBaseCurrency();
    }
}
