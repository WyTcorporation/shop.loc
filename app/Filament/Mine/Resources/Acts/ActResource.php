<?php

namespace App\Filament\Mine\Resources\Acts;

use App\Filament\Mine\Resources\Acts\Pages\CreateAct;
use App\Filament\Mine\Resources\Acts\Pages\EditAct;
use App\Filament\Mine\Resources\Acts\Pages\ListActs;
use App\Models\Act;
use App\Services\Documents\DocumentExporter;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActResource extends Resource
{
    protected static ?string $model = Act::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

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
                    ->label(__('shop.admin.resources.acts.fields.number'))
                    ->required()
                    ->maxLength(64),
                DatePicker::make('issued_at')
                    ->label(__('shop.admin.resources.acts.fields.issued_at')),
                TextInput::make('status')
                    ->label(__('shop.common.status'))
                    ->maxLength(64)
                    ->default('draft'),
                TextInput::make('total')
                    ->label(__('shop.admin.resources.acts.fields.total'))
                    ->numeric()
                    ->default(0),
                Textarea::make('description')
                    ->label(__('shop.admin.resources.acts.fields.description'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('shop.admin.resources.acts.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('shop.admin.resources.orders.label'))
                    ->searchable(),
                TextColumn::make('issued_at')
                    ->label(__('shop.admin.resources.acts.fields.issued_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('shop.admin.resources.acts.fields.total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('shop.common.status'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('shop.common.status'))
                    ->options(fn () => Act::query()
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
                        ->action(fn (Act $record) => DocumentExporter::download($record, 'pdf', 'act')),
                    Action::make('download_csv')
                        ->label('CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Act $record) => DocumentExporter::download($record, 'csv', 'act')),
                    Action::make('download_xml')
                        ->label('XML')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Act $record) => DocumentExporter::download($record, 'xml', 'act')),
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
        return __('shop.admin.resources.acts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.acts.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.accounting');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', Act::class) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', Act::class)) {
            return null;
        }

        return (string) Act::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActs::route('/'),
            'create' => CreateAct::route('/create'),
            'edit' => EditAct::route('/{record}/edit'),
        ];
    }
}
