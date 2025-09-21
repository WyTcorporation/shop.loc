<?php

namespace App\Filament\Mine\Resources\Warehouses;

use App\Filament\Mine\Resources\Warehouses\Pages\CreateWarehouse;
use App\Filament\Mine\Resources\Warehouses\Pages\EditWarehouse;
use App\Filament\Mine\Resources\Warehouses\Pages\ListWarehouses;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set as FormsSet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get as SchemaGet;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->values();

        return $schema->components([
            TextInput::make('code')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->rule('alpha_dash')
                ->live(onBlur: true)
                ->afterStateUpdated(fn (FormsSet $set, ?string $state) => $set('code', strtoupper((string) $state))),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->hidden()
                ->dehydrateStateUsing(fn ($state, SchemaGet $get) => $get('name_translations.' . $primaryLocale) ?? $state),
            Textarea::make('description')
                ->columnSpanFull()
                ->hidden()
                ->dehydrateStateUsing(fn ($state, SchemaGet $get) => $get('description_translations.' . $primaryLocale) ?? $state),
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
                                    ->afterStateUpdated(function (SchemaSet $set, $state) use ($locale, $primaryLocale): void {
                                        if ($locale === $primaryLocale) {
                                            $set('name', $state);
                                        }
                                    }),
                                Textarea::make("description_translations.{$locale}")
                                    ->label(__('shop.products.fields.description'))
                                    ->columnSpanFull()
                                    ->required($locale === $primaryLocale)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (SchemaSet $set, $state) use ($locale, $primaryLocale): void {
                                        if ($locale === $primaryLocale) {
                                            $set('description', $state);
                                        }
                                    }),
                            ]))
                        ->toArray(),
                ),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('shop.warehouses.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('shop.warehouses.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('shop.warehouses.fields.description'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('shop.common.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('shop.common.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.warehouses.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.warehouses.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.inventory');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Warehouse::count();
    }
}
