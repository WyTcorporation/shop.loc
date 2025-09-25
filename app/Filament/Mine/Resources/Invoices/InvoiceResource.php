<?php

namespace App\Filament\Mine\Resources\Invoices;

use App\Filament\Mine\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Mine\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Mine\Resources\Invoices\Pages\ListInvoices;
use App\Models\Invoice;
use App\Services\Documents\DocumentExporter;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

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
                    ->label(__('shop.common.order_number'))
                    ->required()
                    ->maxLength(64),
                TextInput::make('status')
                    ->label(__('shop.common.status'))
                    ->maxLength(64)
                    ->default('draft'),
                TextInput::make('currency')
                    ->label(__('shop.orders.fields.currency'))
                    ->maxLength(3),
                DatePicker::make('issued_at')
                    ->label(__('shop.orders.fields.issued_at')),
                DatePicker::make('due_at')
                    ->label(__('shop.orders.fields.due_at')),
                TextInput::make('subtotal')
                    ->label(__('shop.common.items_subtotal'))
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_total')
                    ->label(__('shop.orders.fields.tax_total'))
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->label(__('shop.common.total'))
                    ->numeric()
                    ->default(0),
                KeyValue::make('metadata')
                    ->label(__('shop.orders.fields.metadata'))
                    ->addButtonLabel(__('shop.common.add'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('shop.admin.resources.invoices.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('shop.admin.resources.orders.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('shop.common.total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('shop.orders.fields.currency')),
                TextColumn::make('issued_at')
                    ->label(__('shop.orders.fields.issued_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('shop.common.status'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('shop.common.status'))
                    ->options(fn () => Invoice::query()
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
                        ->action(fn (Invoice $record) => DocumentExporter::download($record, 'pdf', 'invoice')),
                    Action::make('download_csv')
                        ->label('CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Invoice $record) => DocumentExporter::download($record, 'csv', 'invoice')),
                    Action::make('download_xml')
                        ->label('XML')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Invoice $record) => DocumentExporter::download($record, 'xml', 'invoice')),
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
        return __('shop.admin.resources.invoices.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.invoices.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.accounting');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', Invoice::class) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', Invoice::class)) {
            return null;
        }

        return (string) Invoice::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
