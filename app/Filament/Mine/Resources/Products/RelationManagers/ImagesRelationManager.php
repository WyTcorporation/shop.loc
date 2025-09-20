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

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $recordTitleAttribute = 'path';

    public function form(Schema $schema): Schema
    {
        $primaryLocale = config('app.locale');
        $supportedLocales = collect(config('app.supported_locales', [$primaryLocale]))
            ->filter()
            ->unique()
            ->values();

        return $schema->schema([
            FileUpload::make('path')
                ->label('Image')
                ->disk('public')
                ->directory(fn () => 'products/' . $this->getOwnerRecord()->id)
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
                        ->map(fn (string $locale): Tab => Tab::make(strtoupper($locale))
                            ->schema([
                                TextInput::make("alt_translations.{$locale}")
                                    ->label('Alt text')
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
            Hidden::make('disk')->default('public'),
            TextInput::make('sort')->numeric()->default(0),
            Toggle::make('is_primary')
                ->label('Primary')
                ->inline(false)
                ->default(fn ($livewire) => ! $livewire->getOwnerRecord()->images()->exists())
                ->helperText('Використовується як превʼю товару')
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->disk(fn (ProductImage $record) => $record->disk ?: 'public')
                    ->circular(),
                ToggleColumn::make('is_primary')
                    ->label('Primary')
                    ->sortable(),
                TextColumn::make('alt')->limit(40),
                TextColumn::make('sort')->sortable(),
                TextColumn::make('disk')->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('sort');
    }
}
