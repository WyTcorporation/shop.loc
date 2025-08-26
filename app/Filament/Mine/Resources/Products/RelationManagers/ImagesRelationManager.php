<?php

namespace App\Filament\Mine\Resources\Products\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $recordTitleAttribute = 'path';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\FileUpload::make('path')
                ->disk('s3')
                ->directory(fn () => 'products/' . $this->getOwnerRecord()->id)
                ->image()
                ->imageEditor()
                ->preserveFilenames()
                ->maxSize(4 * 1024)
                ->required(),
            Forms\Components\TextInput::make('alt')->maxLength(255),
            Forms\Components\TextInput::make('sort')->numeric()->default(0),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->getStateUsing(
                        fn ($record) => Storage::disk($record->disk)
                            ->temporaryUrl($record->path, now()->addMinutes(10))
                    )
                    ->circular(),
                TextColumn::make('alt')->limit(40),
                TextColumn::make('sort')->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('sort');
    }
}
