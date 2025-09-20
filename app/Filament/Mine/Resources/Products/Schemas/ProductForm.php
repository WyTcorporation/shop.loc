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
                    ->label(__('shop.products.fields.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('shop.products.fields.slug'))
                    ->required(),
                TextInput::make('sku')
                    ->label(__('shop.products.fields.sku'))
                    ->required(),
                Select::make('category_id')
                    ->label(__('shop.products.fields.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('vendor_id')
                    ->label(__('shop.products.fields.vendor'))
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
                    ->label(__('shop.products.attributes.label'))
                    ->keyLabel(__('shop.products.attributes.name'))
                    ->valueLabel(__('shop.products.attributes.value'))
                    ->reorderable()
                    ->addActionLabel(__('shop.products.attributes.add'))
                    ->columnSpanFull(),
                Placeholder::make('available_stock')
                    ->label(__('shop.products.placeholders.available_stock'))
                    ->content(fn (?Product $record): string => (string) ($record?->stock ?? 0))
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label(__('shop.products.fields.price'))
                    ->required()
                    ->numeric()
                    ->prefix(fn (?Product $record) => currencySymbol()),
                TextInput::make('price_old')
                    ->label(__('shop.products.fields.price_old'))
                    ->numeric(),
                Toggle::make('is_active')
                    ->label(__('shop.products.fields.is_active'))
                    ->required(),
            ]);
    }
}
