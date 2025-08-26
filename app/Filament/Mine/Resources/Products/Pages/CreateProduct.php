<?php

namespace App\Filament\Mine\Resources\Products\Pages;

use App\Filament\Mine\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
