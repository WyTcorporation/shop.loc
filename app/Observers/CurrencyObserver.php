<?php

namespace App\Observers;

use App\Models\Currency;
use App\Services\Currency\CurrencyConverter;

class CurrencyObserver
{
    public function saved(Currency $currency): void
    {
        app(CurrencyConverter::class)->refreshRates();
    }

    public function deleted(Currency $currency): void
    {
        app(CurrencyConverter::class)->refreshRates();
    }

    public function forceDeleted(Currency $currency): void
    {
        app(CurrencyConverter::class)->refreshRates();
    }
}
