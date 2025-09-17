<?php

namespace App\Support;

use NumberFormatter;

class CurrencyFormatter
{
    /**
     * The default number of decimals to display for monetary amounts.
     */
    private const DEFAULT_DECIMALS = 2;

    /**
     * Common currency symbols to avoid reliance on the intl extension.
     *
     * @var array<string, string>
     */
    private static array $symbols = [
        'AED' => 'د.إ',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'CHF' => 'CHF',
        'CNY' => '¥',
        'CZK' => 'Kč',
        'DKK' => 'kr',
        'EUR' => '€',
        'GBP' => '£',
        'HUF' => 'Ft',
        'JPY' => '¥',
        'NOK' => 'kr',
        'PLN' => 'zł',
        'SEK' => 'kr',
        'UAH' => '₴',
        'USD' => '$',
    ];

    public static function base(): string
    {
        return strtoupper((string) config('shop.currency.base', 'EUR'));
    }

    public static function code(?string $currency = null): string
    {
        return strtoupper($currency ?? self::base());
    }

    public static function symbol(?string $currency = null): string
    {
        $code = self::code($currency);

        if (isset(self::$symbols[$code])) {
            return self::$symbols[$code];
        }

        $locale = (string) config('app.locale', 'en');

        try {
            $formatter = new NumberFormatter($locale . '@currency=' . $code, NumberFormatter::CURRENCY);
            $symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

            if (is_string($symbol) && $symbol !== '') {
                return $symbol;
            }
        } catch (\Throwable $exception) {
            // Fallback to the currency code when the intl extension is missing or unsupported.
        }

        return $code;
    }

    public static function format(float|int|string|null $amount, ?string $currency = null, int $decimals = self::DEFAULT_DECIMALS): string
    {
        $value = number_format((float) ($amount ?? 0), $decimals);

        return trim(self::symbol($currency) . ' ' . $value);
    }
}
