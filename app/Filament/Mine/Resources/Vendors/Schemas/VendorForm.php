<?php

namespace App\Filament\Mine\Resources\Vendors\Schemas;

use App\Support\Phone;
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
                    ->label(__('shop.vendor.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->hidden()
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('name_translations.' . $primaryLocale) ?? $state),
                TextInput::make('slug')
                    ->label(__('shop.vendor.fields.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('contact_email')
                    ->label(__('shop.common.email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('contact_phone')
                    ->label(__('shop.common.phone'))
                    ->placeholder('+123 456 789 012')
                    ->tel()
                    ->live(onBlur: true)
                    ->afterStateHydrated(fn (TextInput $component, $state) => $component->state(Phone::format($state)))
                    ->afterStateUpdated(function (Set $set, $state): void {
                        $set('contact_phone', Phone::format($state));
                    })
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('shop.vendor.fields.description'))
                    ->rows(4)
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
