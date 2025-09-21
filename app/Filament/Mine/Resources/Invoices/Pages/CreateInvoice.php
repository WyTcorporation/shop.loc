<?php

namespace App\Filament\Mine\Resources\Invoices\Pages;

use App\Filament\Mine\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
