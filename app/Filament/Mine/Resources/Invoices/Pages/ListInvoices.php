<?php

namespace App\Filament\Mine\Resources\Invoices\Pages;

use App\Filament\Mine\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()?->can('create', InvoiceResource::getModel())),
        ];
    }
}
