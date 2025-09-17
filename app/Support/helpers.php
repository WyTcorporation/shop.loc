<?php

use App\Support\CurrencyFormatter;

if (! function_exists('currencySymbol')) {
    function currencySymbol(?string $currency = null): string
    {
        return CurrencyFormatter::symbol($currency);
    }
}

if (! function_exists('baseCurrency')) {
    function baseCurrency(): string
    {
        return CurrencyFormatter::base();
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency(float|int|string|null $amount, ?string $currency = null, int $decimals = 2): string
    {
        return CurrencyFormatter::format($amount, $currency, $decimals);
    }
}
