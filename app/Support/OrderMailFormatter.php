<?php

namespace App\Support;

use App\Models\Order;
use App\Services\Currency\CurrencyConverter;

class OrderMailFormatter
{
    public static function money(Order $order, float $amount, ?string $currency = null): string
    {
        /** @var CurrencyConverter $converter */
        $converter = app(CurrencyConverter::class);

        $baseCurrency = strtoupper($converter->getBaseCurrency());
        $orderCurrency = strtoupper($currency ?? $order->currency ?? $baseCurrency);
        $baseAmount = $converter->convertToBase($amount, $orderCurrency);

        $formatCurrency = static function (float $value, string $code): string {
            $formatted = number_format($value, 2, ',', ' ');

            return $code === 'UAH'
                ? sprintf('%s грн', $formatted)
                : sprintf('%s %s', $formatted, $code);
        };

        $amounts = [
            'UAH' => $formatCurrency($converter->convertFromBase($baseAmount, 'UAH'), 'UAH'),
            $baseCurrency => $formatCurrency($baseAmount, $baseCurrency),
        ];

        return implode(' / ', array_unique(array_values($amounts)));
    }
}
