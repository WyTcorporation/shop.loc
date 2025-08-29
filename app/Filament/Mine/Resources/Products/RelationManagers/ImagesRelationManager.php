<?php

namespace App\Filament\Mine\Resources\Products\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $recordTitleAttribute = 'path';

    public function form(Schema $schema): Schema
    {
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

            TextInput::make('alt')->maxLength(255),
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
                    ->disk('public')
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
