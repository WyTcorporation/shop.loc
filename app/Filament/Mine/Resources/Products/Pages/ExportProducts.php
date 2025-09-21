<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\ProductResource;
use App\Jobs\StartProductExport;
use App\Models\Product;
use App\Models\ProductExport;
use App\Models\ProductImport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ExportProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.mine.resources.products.pages.export-products';

    public array $data = [];

    public string $activeTab = 'form';

    public $recentExports;

    public $recentImports;

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);

        $this->form->fill([
            'format' => 'csv',
            'only_active' => true,
        ]);

        $this->loadHistory();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('file_name')
                        ->label(__('shop.admin.resources.products.exports.fields.file_name'))
                        ->placeholder('products_export')
                        ->maxLength(120),
                    Select::make('format')
                        ->label(__('shop.admin.resources.products.exports.fields.format'))
                        ->options([
                            'csv' => 'CSV',
                            'xlsx' => 'XLSX',
                        ])
                        ->required(),
                    Toggle::make('only_active')
                        ->label(__('shop.admin.resources.products.exports.fields.only_active'))
                        ->default(true),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        Gate::authorize('viewAny', Product::class);

        $data = $this->form->getState();

        $export = ProductExport::query()->create([
            'user_id' => Auth::id(),
            'format' => $data['format'],
            'file_name' => $data['file_name'] ?? null,
            'disk' => 'public',
            'status' => 'queued',
            'filters' => [
                'only_active' => (bool) ($data['only_active'] ?? false),
            ],
        ]);

        StartProductExport::dispatch($export->id);

        Notification::make()
            ->title(__('shop.admin.resources.products.exports.messages.queued_title'))
            ->body(__('shop.admin.resources.products.exports.messages.queued_body'))
            ->success()
            ->send();

        $this->loadHistory();
        $this->activeTab = 'history';
    }

    public function loadHistory(): void
    {
        $this->recentExports = ProductExport::query()
            ->latest()
            ->limit(10)
            ->get();

        $this->recentImports = ProductImport::query()
            ->latest()
            ->limit(10)
            ->get();
    }
}
