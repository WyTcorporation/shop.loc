<?php

namespace App\Filament\Mine\Resources\Currencies\Pages;

use App\Filament\Mine\Resources\Currencies\CurrencyResource;
use App\Services\Currency\CurrencyConverter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn () => app(CurrencyConverter::class)->refreshRates()),
        ];
    }

    protected function afterSave(): void
    {
        app(CurrencyConverter::class)->refreshRates();
    }
}
