<?php

namespace App\Filament\Mine\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function currencySymbol;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->values();

        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('shop.products.fields.name'))
                    ->required()
                    ->hidden()
                    ->afterStateHydrated(function (TextInput $component, $state, Set $set) use ($primaryLocale): void {
                        if (filled($state)) {
                            $set("name_translations.{$primaryLocale}", $state);
                        }
                    })
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
                Textarea::make('description')
                    ->label(__('shop.products.fields.description'))
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
                                        ->label(__('shop.products.fields.name'))
                                        ->required($locale === $primaryLocale)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                            if ($locale === $primaryLocale) {
                                                $set('name', $state);
                                            }
                                        }),
                                    Textarea::make("description_translations.{$locale}")
                                        ->label(__('shop.products.fields.description'))
                                        ->required($locale === $primaryLocale)
                                        ->columnSpanFull()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                            if ($locale === $primaryLocale) {
                                                $set('description', $state);
                                            }
                                        }),
                                ]))
                            ->toArray(),
                    ),
                TextInput::make('slug')
                    ->label(__('shop.products.fields.slug'))
                    ->required(),
                TextInput::make('sku')
                    ->label(__('shop.products.fields.sku'))
                    ->required(),
                Select::make('category_id')
                    ->label(__('shop.products.fields.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('vendor_id')
                    ->label(__('shop.products.fields.vendor'))
                    ->relationship(
                        name: 'vendor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->visibleTo(Auth::user()),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->default(fn () => Auth::user()?->vendor?->id)
                    ->disabled(fn () => Auth::user()?->vendor !== null)
                    ->dehydrated(fn () => Auth::user()?->vendor === null),
                KeyValue::make('attributes')
                    ->label(__('shop.products.attributes.label'))
                    ->keyLabel(__('shop.products.attributes.name'))
                    ->valueLabel(__('shop.products.attributes.value'))
                    ->reorderable()
                    ->addActionLabel(__('shop.products.attributes.add'))
                    ->columnSpanFull(),
                Placeholder::make('available_stock')
                    ->label(__('shop.products.placeholders.available_stock'))
                    ->content(fn (?Product $record): string => (string) ($record?->stock ?? 0))
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label(__('shop.products.fields.price'))
                    ->required()
                    ->numeric()
                    ->prefix(fn (?Product $record) => currencySymbol()),
                TextInput::make('price_old')
                    ->label(__('shop.products.fields.price_old'))
                    ->numeric(),
                Toggle::make('is_active')
                    ->label(__('shop.products.fields.is_active'))
                    ->required(),
            ]);
    }
}
