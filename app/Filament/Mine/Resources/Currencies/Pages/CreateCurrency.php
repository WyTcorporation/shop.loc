<?php

namespace App\Filament\Mine\Resources\Currencies\Pages;

use App\Filament\Mine\Resources\Currencies\CurrencyResource;
use App\Services\Currency\CurrencyConverter;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function afterCreate(): void
    {
        app(CurrencyConverter::class)->refreshRates();
    }
}
