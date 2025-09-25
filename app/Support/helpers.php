<?php

use App\Support\CurrencyFormatter;
use Illuminate\Http\Request;

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

if (! function_exists('resolveMailLocale')) {
    function resolveMailLocale(?Request $request = null): string
    {
        $request ??= request();

        $locale = null;

        if ($request instanceof Request) {
            $cookieLocale = $request->cookie('lang');

            if (is_string($cookieLocale) && $cookieLocale !== '') {
                $locale = $cookieLocale;
            }
        }

        if (!is_string($locale) || $locale === '') {
            $locale = app()->getLocale();
        }

        if (!is_string($locale) || $locale === '') {
            $locale = (string) config('app.fallback_locale', 'en');
        }

        return $locale;
    }
}

if (! function_exists('localeLabel')) {
    function localeLabel(string $locale): string
    {
        return match ($locale) {
            'uk' => 'UA',
            default => strtoupper($locale),
        };
    }
}
