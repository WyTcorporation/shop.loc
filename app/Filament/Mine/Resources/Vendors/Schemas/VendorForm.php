<?php

namespace App\Filament\Mine\Resources\Vendors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->values();

        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('shop.common.owner'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn () => (bool) $user?->vendor),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->hidden()
                    ->afterStateHydrated(function (TextInput $component, $state, Set $set, Get $get) use ($primaryLocale): void {
                        if (filled($get("name_translations.{$primaryLocale}"))) {
                            return;
                        }

                        $rawName = $component->getRecord()?->getRawOriginal('name');

                        if (filled($rawName)) {
                            $set("name_translations.{$primaryLocale}", $rawName);
                        }
                    })
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('contact_email')
                    ->label(__('shop.common.email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('contact_phone')
                    ->label(__('shop.common.phone'))
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull()
                    ->hidden()
                    ->afterStateHydrated(function (Textarea $component, $state, Set $set, Get $get) use ($primaryLocale): void {
                        if (filled($get("description_translations.{$primaryLocale}"))) {
                            return;
                        }

                        $rawDescription = $component->getRecord()?->getRawOriginal('description');

                        if (filled($rawDescription)) {
                            $set("description_translations.{$primaryLocale}", $rawDescription);
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
            ])
            ->columns(2);
    }
}
