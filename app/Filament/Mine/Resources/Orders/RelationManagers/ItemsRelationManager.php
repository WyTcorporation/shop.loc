<?php

namespace App\Filament\Mine\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Filament\Mine\Resources\Orders\Pages\EditOrder;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use function currencySymbol;
use function formatCurrency;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('shop.orders.items.title');
    }

    public function form(Schema $schema): Schema
    {
        $symbol = currencySymbol($this->getOwnerRecord()?->currency);

        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('shop.orders.items.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $price = Product::find($state)?->price ?? 0;
                        $set('price', $price);
                    })
                    ->required(),
                TextInput::make('qty')
                    ->label(__('shop.orders.items.fields.qty'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->reactive(),

                TextInput::make('price')
                    ->label(__('shop.orders.items.fields.price'))
                    ->numeric()
                    ->rule('decimal:0,2')
                    ->required()
                    ->prefix($symbol),
            ])->columns(3);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('OrderItem')
            ->columns([
                TextColumn::make('product.name')->label(__('shop.orders.items.fields.product')),
                TextColumn::make('qty')->label(__('shop.orders.items.fields.qty')),
                TextColumn::make('price')
                    ->state(fn (OrderItem $record) => formatCurrency($record->price, $record->order?->currency))
                    ->label(__('shop.orders.items.fields.price')),
                TextColumn::make('subtotal')
                    ->label(__('shop.orders.items.fields.subtotal'))
                    ->state(fn (OrderItem $record) => formatCurrency($record->qty * (float) $record->price, $record->order?->currency)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
                AttachAction::make()->after(function (RelationManager $livewire) {
                    $order = $livewire->getOwnerRecord();
                    $order->recalculateTotal();
                    $livewire->dispatch('order-items-updated')
                        ->to(EditOrder::class);
                }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
                DeleteAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->status === OrderStatus::New)
                    ->after(function (RelationManager $livewire) {
                        $order = $livewire->getOwnerRecord();
                        $order->recalculateTotal();
                        $livewire->dispatch('order-items-updated')
                            ->to(EditOrder::class);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->emptyStateHeading(__('shop.orders.items.empty_state.heading'));
    }
}
