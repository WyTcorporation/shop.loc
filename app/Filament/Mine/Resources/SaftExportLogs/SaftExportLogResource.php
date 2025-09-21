<?php

namespace App\Filament\Mine\Resources\SaftExportLogs;

use App\Filament\Mine\Resources\SaftExportLogs\Pages\ExportSaft;
use App\Filament\Mine\Resources\SaftExportLogs\Pages\ListSaftExportLogs;
use App\Models\SaftExportLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class SaftExportLogResource extends Resource
{
    protected static ?string $model = SaftExportLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownOnSquareStack;

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('format')
                    ->label(__('shop.admin.resources.saft_exports.fields.format'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('shop.common.status'))
                    ->badge()
                    ->color(fn (SaftExportLog $record) => match ($record->status) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('exported_at')
                    ->label(__('shop.admin.resources.saft_exports.fields.exported_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('shop.admin.resources.saft_exports.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('message')
                    ->label(__('shop.admin.resources.saft_exports.fields.message'))
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('shop.common.status'))
                    ->options([
                        'completed' => __('shop.admin.resources.saft_exports.status.completed'),
                        'processing' => __('shop.admin.resources.saft_exports.status.processing'),
                        'failed' => __('shop.admin.resources.saft_exports.status.failed'),
                    ]),
            ])
            ->actions([
                Action::make('download')
                    ->label(__('shop.common.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (SaftExportLog $record) => filled($record->file_path) && $record->status === 'completed')
                    ->action(function (SaftExportLog $record) {
                        $path = $record->file_path;

                        if (! $path || ! Storage::disk('local')->exists($path)) {
                            abort(404);
                        }

                        return response()->download(storage_path('app/' . $path));
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'order']);
    }

    public static function getModelLabel(): string
    {
        return __('shop.admin.resources.saft_exports.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('shop.admin.resources.saft_exports.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('shop.admin.navigation.accounting');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('viewAny', SaftExportLog::class) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('viewAny', SaftExportLog::class)) {
            return null;
        }

        return (string) SaftExportLog::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaftExportLogs::route('/'),
            'export' => ExportSaft::route('/export'),
        ];
    }
}
