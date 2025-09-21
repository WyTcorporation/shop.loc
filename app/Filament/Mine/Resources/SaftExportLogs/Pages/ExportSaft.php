<?php

namespace App\Filament\Mine\Resources\SaftExportLogs\Pages;

use App\Filament\Mine\Resources\SaftExportLogs\SaftExportLogResource;
use App\Models\Order;
use App\Models\SaftExportLog;
use App\Services\Documents\SaftExporter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

class ExportSaft extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SaftExportLogResource::class;

    protected string $view = 'filament.mine.resources.saft-export-logs.pages.export';

    public array $data = [];

    public ?SaftExportLog $latestLog = null;

    public function mount(): void
    {
        Gate::authorize('create', SaftExportLog::class);

        $this->form->fill([
            'format' => 'xml',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('format')
                        ->label(__('shop.admin.resources.saft_exports.fields.format'))
                        ->options([
                            'pdf' => 'PDF',
                            'csv' => 'CSV',
                            'xml' => 'XML',
                        ])
                        ->required(),
                    Select::make('order_id')
                        ->label(__('shop.admin.resources.orders.label'))
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => Order::query()
                            ->where('number', 'like', "%{$search}%")
                            ->orWhere('id', $search)
                            ->limit(20)
                            ->pluck('number', 'id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value) => Order::query()->find($value)?->number)
                        ->placeholder(__('shop.orders.placeholders.any_order')),
                    DatePicker::make('from')
                        ->label(__('shop.admin.resources.saft_exports.fields.from_date')),
                    DatePicker::make('to')
                        ->label(__('shop.admin.resources.saft_exports.fields.to_date')),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        Gate::authorize('create', SaftExportLog::class);

        $data = $this->form->getState();

        $exporter = app(SaftExporter::class);

        $log = $exporter->export([
            'order_id' => $data['order_id'] ?? null,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
        ], $data['format']);

        $this->latestLog = $log->fresh();

        Notification::make()
            ->title(__('shop.admin.resources.saft_exports.messages.success'))
            ->body(__('shop.admin.resources.saft_exports.messages.completed_info'))
            ->success()
            ->send();
    }
}
