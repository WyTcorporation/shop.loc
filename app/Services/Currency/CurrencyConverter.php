<?php

namespace App\Services\Currency;

use App\Models\Currency;

class CurrencyConverter
{
    protected string $baseCurrency;

    /**
     * @var array<string, float>
     */
    protected array $rates = [];

    protected bool $ratesLoaded = false;

    public function __construct()
    {
        $this->baseCurrency = strtoupper((string) config('shop.currency.base', 'EUR'));
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function normalizeCurrency(?string $currency): string
    {
        $currency = strtoupper((string) $currency);

        if ($currency === '') {
            return $this->baseCurrency;
        }

        return $currency;
    }

    public function convertFromBase(float|int $amount, string $currency): float
    {
        $currency = $this->normalizeCurrency($currency);

        if ($currency === $this->baseCurrency) {
            return round((float) $amount, 2);
        }

        $rate = $this->getRate($currency);

        if ($rate === null || $rate <= 0) {
            return round((float) $amount, 2);
        }

        return round((float) $amount * $rate, 2);
    }

    public function convertToBase(float|int $amount, string $currency): float
    {
        $currency = $this->normalizeCurrency($currency);

        if ($currency === $this->baseCurrency) {
            return round((float) $amount, 2);
        }

        $rate = $this->getRate($currency);

        if ($rate === null || $rate <= 0) {
            return round((float) $amount, 2);
        }

        return round((float) $amount / $rate, 4);
    }

    public function convertBaseCents(int $amount, string $currency): int
    {
        $value = $this->convertFromBase($amount / 100, $currency);

        return (int) round($value * 100);
    }

    public function convertToBaseCents(int $amount, string $currency): int
    {
        $value = $this->convertToBase($amount / 100, $currency);

        return (int) round($value * 100);
    }

    public function getRate(string $currency): ?float
    {
        $currency = $this->normalizeCurrency($currency);

        $this->ensureRatesLoaded();

        return $this->rates[$currency] ?? null;
    }

    public function refreshRates(): void
    {
        $this->rates = [];
        $this->ratesLoaded = false;
    }

    protected function ensureRatesLoaded(): void
    {
        if ($this->ratesLoaded) {
            return;
        }

        $this->rates = Currency::query()
            ->get(['code', 'rate'])
            ->mapWithKeys(fn (Currency $currency) => [strtoupper($currency->code) => (float) $currency->rate])
            ->all();

        $this->rates[$this->baseCurrency] = 1.0;
        $this->ratesLoaded = true;
    }
}
