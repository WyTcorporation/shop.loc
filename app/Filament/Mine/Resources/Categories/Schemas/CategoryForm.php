<?php

namespace App\Filament\Mine\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CategoryForm
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
                    ->label(__('shop.categories.fields.name'))
                    ->required()
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
                Tabs::make('translations')
                    ->columnSpanFull()
                    ->tabs(
                        $supportedLocales
                            ->map(fn (string $locale): Tab => Tab::make(strtoupper($locale))
                                ->schema([
                                    TextInput::make("name_translations.{$locale}")
                                        ->label(__('shop.categories.fields.name'))
                                        ->required($locale === $primaryLocale)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                            if ($locale === $primaryLocale) {
                                                $set('name', $state);
                                            }
                                        }),
                                ]))
                            ->toArray(),
                    ),
                TextInput::make('slug')
                    ->label(__('shop.categories.fields.slug'))
                    ->required(),
                Select::make('parent_id')
                    ->label(__('shop.categories.fields.parent'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable()
                    ->rule(fn (?Model $record) => Rule::notIn([$record?->id])),
            ]);
    }
}
