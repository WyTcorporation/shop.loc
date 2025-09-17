<?php

namespace App\Filament\Mine\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function currencySymbol;

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
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('vendor_id')
                    ->label('Vendor')
                    ->relationship(
                        name: 'vendor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->visibleTo(Auth::user()),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->default(fn () => Auth::user()?->vendor?->id)
                    ->disabled(fn () => Auth::user()?->vendor !== null)
                    ->dehydrated(fn () => Auth::user()?->vendor === null),
                KeyValue::make('attributes')
                    ->label('Attributes')
                    ->keyLabel('Name')
                    ->valueLabel('Value')
                    ->reorderable()
                    ->addActionLabel('Add attribute')
                    ->columnSpanFull(),
                Placeholder::make('available_stock')
                    ->label('Available stock')
                    ->content(fn (?Product $record): string => (string) ($record?->stock ?? 0))
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix(fn (?Product $record) => currencySymbol()),
                TextInput::make('price_old')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
