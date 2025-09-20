<?php

namespace App\Filament\Mine\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('shop.categories.fields.name'))
                    ->required(),
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
