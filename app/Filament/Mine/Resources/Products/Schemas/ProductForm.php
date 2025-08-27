<?php

namespace App\Filament\Mine\Resources\Products\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('category_id')
                    ->numeric(),
                // ВАРІАНТ А: асоціативний JSON (key => value)
                KeyValue::make('attributes')
                    ->label('Attributes')
                    ->keyLabel('Name')
                    ->valueLabel('Value')
                    ->reorderable()
                    ->addActionLabel('Add attribute')
                    ->columnSpanFull(),

                // ВАРІАНТ Б: як список пар (якщо хочеш контроль валідації/типів)
                // Repeater::make('attributes')
                //     ->schema([
                //         TextInput::make('name')->required(),
                //         TextInput::make('value'),
                //     ])
                //     ->default([])
                //     ->columns(2)
                //     ->columnSpanFull(),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('price_old')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
