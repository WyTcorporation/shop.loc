<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\ProductResource;
use App\Jobs\StartProductImport;
use App\Models\Product;
use App\Models\ProductExport;
use App\Models\ProductImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImportProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.mine.resources.products.pages.import-products';

    public array $data = [];

    public string $activeTab = 'form';

    public $recentImports;

    public $recentExports;

    public function mount(): void
    {
        Gate::authorize('create', Product::class);

        $this->form->fill();
        $this->loadHistory();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make()->schema([
                    FileUpload::make('file')
                        ->label(__('shop.admin.resources.products.imports.fields.file'))
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->directory('imports/products')
                        ->disk('local')
                        ->storeFileNamesIn('original_name')
                        ->preserveFilenames()
                        ->maxSize(10240)
                        ->required(),
                ])->columns(1),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        Gate::authorize('create', Product::class);

        $data = $this->form->getState();

        if (empty($data['file']) || empty($data['original_name'])) {
            Notification::make()->title(__('shop.admin.resources.products.imports.messages.missing_file'))->danger()->send();

            return;
        }

        $import = ProductImport::query()->create([
            'user_id' => Auth::id(),
            'original_name' => $data['original_name'],
            'file_path' => $data['file'],
            'disk' => 'local',
            'status' => 'queued',
        ]);

        StartProductImport::dispatch($import->id);

        Notification::make()
            ->title(__('shop.admin.resources.products.imports.messages.queued_title'))
            ->body(__('shop.admin.resources.products.imports.messages.queued_body'))
            ->success()
            ->send();

        $this->form->fill([]);
        $this->loadHistory();
        $this->activeTab = 'history';
    }

    public function loadHistory(): void
    {
        $this->recentImports = ProductImport::query()
            ->latest()
            ->limit(10)
            ->get();

        $this->recentExports = ProductExport::query()
            ->latest()
            ->limit(10)
            ->get();
    }
}
