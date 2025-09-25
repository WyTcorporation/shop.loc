<?php

namespace App\Filament\Mine\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function currencySymbol;
use function data_get;
use function localeLabel;

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
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
                Textarea::make('description')
                    ->label(__('shop.products.fields.description'))
                    ->columnSpanFull()
                    ->hidden()
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('description_translations.' . $primaryLocale) ?? $state),
                Tabs::make('translations')
                    ->columnSpanFull()
                    ->tabs(
                        $supportedLocales
                            ->map(fn (string $locale): Tab => Tab::make(localeLabel($locale))
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
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = Auth::user();

                            if ($user === null) {
                                return $query;
                            }

                            $permittedCategoryIds = $user->permittedCategoryIds();

                            if ($permittedCategoryIds->isEmpty()) {
                                return $query;
                            }

                            return $query->whereIn('id', $permittedCategoryIds);
                        }
                    )
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
                Repeater::make('attributes')
                    ->label(__('shop.products.attributes.label'))
                    ->schema([
                        TextInput::make('key')
                            ->label(__('shop.products.attributes.name'))
                            ->required()
                            ->live(onBlur: true),
                        TextInput::make('value')
                            ->label(__('shop.products.attributes.value'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (TextInput $component, $state, Set $set, Get $get) use ($primaryLocale): void {
                                $primaryTranslation = data_get($get('translations'), $primaryLocale);

                                if (filled($state) && blank($primaryTranslation)) {
                                    $set("translations.{$primaryLocale}", $state);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, $state) use ($primaryLocale): void {
                                if (blank($state)) {
                                    $set("translations.{$primaryLocale}", $state);

                                    return;
                                }

                                $primaryTranslation = data_get($get('translations'), $primaryLocale);

                                if (blank($primaryTranslation)) {
                                    $set("translations.{$primaryLocale}", $state);
                                }
                            }),
                        Fieldset::make('translations')
                            ->label(__('shop.products.attributes.translations'))
                            ->schema(
                                $supportedLocales
                                    ->map(fn (string $locale): TextInput => TextInput::make($locale)
                                        ->label(localeLabel($locale))
                                        ->live(onBlur: true)
                                        ->afterStateHydrated(function (TextInput $component, $state, Set $set, Get $get) use ($locale, $primaryLocale): void {
                                            if ($locale !== $primaryLocale || filled($state)) {
                                                return;
                                            }

                                            $value = $get('value');

                                            if (filled($value)) {
                                                $set("translations.{$locale}", $value);
                                            }
                                        })
                                        ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                            if ($locale === $primaryLocale) {
                                                $set('value', $state);
                                            }
                                        }))
                                    ->toArray()
                            )
                            ->columns(2)
                            ->statePath('translations'),
                    ])
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
