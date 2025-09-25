<?php

namespace App\Filament\Mine\Resources\DeliveryNotes;

use App\Filament\Mine\Resources\DeliveryNotes\Pages\CreateDeliveryNote;
use App\Filament\Mine\Resources\DeliveryNotes\Pages\EditDeliveryNote;
use App\Filament\Mine\Resources\DeliveryNotes\Pages\ListDeliveryNotes;
use App\Models\DeliveryNote;
use App\Services\Documents\DocumentExporter;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('order_id')
                    ->label(__('shop.admin.resources.orders.label'))
                    ->relationship('order', 'number')
                    ->searchable()
                    ->required(),
                TextInput::make('number')
                    ->label(__('shop.admin.resources.delivery_notes.fields.number'))
                    ->required()
                    ->maxLength(64),
                TextInput::make('status')
                    ->label(__('shop.common.status'))
                    ->maxLength(64)
                    ->default('draft'),
                DatePicker::make('issued_at')
                    ->label(__('shop.admin.resources.delivery_notes.fields.issued_at')),
                DatePicker::make('dispatched_at')
                    ->label(__('shop.admin.resources.delivery_notes.fields.dispatched_at')),
                KeyValue::make('items')
                    ->label(__('shop.admin.resources.delivery_notes.fields.items'))
                    ->columnSpanFull(),
                Textarea::make('remarks')
                    ->label(__('shop.admin.resources.delivery_notes.fields.remarks'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('shop.admin.resources.delivery_notes.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('shop.admin.resources.orders.label'))
                    ->searchable(),
                TextColumn::make('issued_at')
                    ->label(__('shop.admin.resources.delivery_notes.fields.issued_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('dispatched_at')
                    ->label(__('shop.admin.resources.delivery_notes.fields.dispatched_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('shop.common.status'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('shop.common.status'))
                    ->options(fn () => DeliveryNote::query()
                        ->select('status')
                        ->whereNotNull('status')
                        ->distinct()
                        ->pluck('status', 'status')
                        ->toArray()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ActionGroup::make([
                    Action::make('download_pdf')
                        ->label('PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (DeliveryNote $record) => DocumentExporter::download($record, 'pdf', 'delivery-note')),
                    Action::make('download_csv')
                        ->label('CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (DeliveryNote $record) => DocumentExporter::download($record, 'csv', 'delivery-note')),
                    Action::make('download_xml')
                        ->label('XML')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (DeliveryNote $record) => DocumentExporter::download($record, 'xml', 'delivery-note')),
                ])->label(__('shop.common.export'))->icon('heroicon-o-document-arrow-down'),
            ])
            ->defaultSort('issued_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('order');
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.delivery_notes.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.delivery_notes.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.accounting');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', DeliveryNote::class) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', DeliveryNote::class)) {
            return null;
        }

        return (string) DeliveryNote::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryNotes::route('/'),
            'create' => CreateDeliveryNote::route('/create'),
            'edit' => EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}
