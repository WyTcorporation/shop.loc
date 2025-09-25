<?php

namespace App\Filament\Mine\Resources\Products\RelationManagers;

use App\Models\ProductImage;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;
use Throwable;
use function localeLabel;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $recordTitleAttribute = 'path';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('shop.products.images.title');
    }

    public function form(Schema $schema): Schema
    {
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->unique()
            ->values();

        return $schema->schema([
            FileUpload::make('path')
                ->label(__('shop.products.images.fields.image'))
                ->disk(fn (): string => ProductImage::defaultDisk())
                ->directory(function (): string {
                    $directory = 'products/' . $this->getOwnerRecord()->id;
                    $diskName = ProductImage::defaultDisk();
                    $storage = Storage::disk($diskName);

                    $ensureDirectory = function (string $path) use ($storage): bool {
                        if ($storage->exists($path)) {
                            return true;
                        }

                        try {
                            return (bool) $storage->makeDirectory($path);
                        } catch (Throwable $exception) {
                            report($exception);

                            return false;
                        }
                    };

                    if ($ensureDirectory($directory)) {
                        return $directory;
                    }

                    $fallbackDirectory = 'products';

                    $ensureDirectory($fallbackDirectory);

                    return $fallbackDirectory;
                })
                ->image()
                ->imageEditor()
                ->preserveFilenames()
                ->maxSize(4 * 1024)
                ->required(),

            TextInput::make('alt')
                ->hidden()
                ->afterStateHydrated(function ($component, $state, Set $set, Get $get) use ($primaryLocale): void {
                    $translations = $get('alt_translations') ?? [];
                    $primaryTranslation = $translations[$primaryLocale] ?? null;

                    if (filled($primaryTranslation)) {
                        $set('alt', $primaryTranslation);
                    } elseif (filled($state)) {
                        $set("alt_translations.{$primaryLocale}", $state);
                    }
                })
                ->dehydrateStateUsing(fn ($state, Get $get) => $get('alt_translations.' . $primaryLocale) ?? $state),

            Tabs::make('alt_translations_tabs')
                ->columnSpanFull()
                ->tabs(
                    $supportedLocales
                        ->map(fn (string $locale): Tab => Tab::make(localeLabel($locale))
                            ->schema([
                                TextInput::make("alt_translations.{$locale}")
                                    ->label(__('shop.products.images.fields.alt_text'))
                                    ->maxLength(255)
                                    ->required($locale === $primaryLocale)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state) use ($locale, $primaryLocale): void {
                                        if ($locale === $primaryLocale) {
                                            $set('alt', $state);
                                        }
                                    }),
                            ]))
                        ->toArray(),
                ),
            Hidden::make('disk')
                ->label(__('shop.products.images.fields.disk'))
                ->default(fn (): string => ProductImage::defaultDisk()),
            TextInput::make('sort')
                ->label(__('shop.products.images.fields.sort'))
                ->numeric()->default(0),
            Toggle::make('is_primary')
                ->label(__('shop.products.images.fields.is_primary'))
                ->inline(false)
                ->default(fn ($livewire) => ! $livewire->getOwnerRecord()->images()->exists())
                ->helperText(__('shop.products.images.helper_texts.is_primary'))
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label(__('shop.products.images.fields.preview'))
                    ->disk(fn (ProductImage $record): string => $record->disk ?: ProductImage::defaultDisk())
                    ->circular(),
                ToggleColumn::make('is_primary')
                    ->label(__('shop.products.images.fields.is_primary'))
                    ->sortable(),
                TextColumn::make('alt')->label(__('shop.products.images.fields.alt_text'))->limit(40),
                TextColumn::make('sort')->label(__('shop.products.images.fields.sort'))->sortable(),
                TextColumn::make('disk')->label(__('shop.products.images.fields.disk'))->sortable(),
                TextColumn::make('created_at')
                    ->label(__('shop.products.images.fields.created_at'))
                    ->dateTime('Y-m-d H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('shop.products.images.actions.create'))
                    ->modalHeading(__('shop.products.images.actions.create')),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('shop.products.images.actions.edit'))
                    ->modalHeading(__('shop.products.images.actions.edit')),
                DeleteAction::make()
                    ->label(__('shop.products.images.actions.delete')),
            ])
            ->emptyStateHeading(__('shop.products.images.empty.heading'))
            ->emptyStateDescription(__('shop.products.images.empty.description'))
            ->defaultSort('sort');
    }
}
