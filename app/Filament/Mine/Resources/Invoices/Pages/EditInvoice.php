<?php

namespace App\Filament\Mine\Resources\Invoices\Pages;

use App\Filament\Mine\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
}
