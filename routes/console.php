<?php

use App\Models\Currency;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:warm', function () {
    $base = rtrim(URL::to('/'), '/');

    $this->info("Warming sitemap index: {$base}/sitemap.xml");
    try {
        Http::timeout(30)->get("{$base}/sitemap.xml");
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
    }

    try {
        $xml = (string) Http::timeout(30)->get("{$base}/sitemap.xml")->body();
        $locs = [];

        if ($xml) {
            $doc = @simplexml_load_string($xml);
            if ($doc && isset($doc->sitemap)) {
                foreach ($doc->sitemap as $sm) {
                    $locs[] = (string) $sm->loc;
                }
            }
        }

        if (empty($locs)) {
            $locs = [
                "{$base}/sitemaps/categories.xml",
            ];
            for ($i = 1; $i <= 10; $i++) {
                $locs[] = "{$base}/sitemaps/products-{$i}.xml";
            }
        }

        foreach ($locs as $url) {
            $this->info("GET {$url}");
            try {
                Http::timeout(30)->get($url);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
    }

    $this->info('Sitemap warming done.');
})->purpose('Warm sitemap caches');

Artisan::command('currency:update {--base=} {codes?*}', function (CurrencyConverter $converter) {
    $baseOption = $this->option('base');
    $base = strtoupper($baseOption ?: (string) config('shop.currency.base', 'EUR'));
    $timeout = (int) config('shop.currency.timeout', 15);
    $provider = (string) config('shop.currency.provider', 'https://open.er-api.com/v6/latest/{base}');

    $requestedCodes = collect($this->argument('codes') ?? [])
        ->map(fn ($code) => strtoupper((string) $code))
        ->filter()
        ->values();

    $url = str_replace('{base}', $base, $provider);

    $this->info("Fetching currency rates for base {$base}...");

    try {
        $response = Http::timeout($timeout)->get($url);
    } catch (\Throwable $e) {
        $this->error('Failed to fetch currency rates: ' . $e->getMessage());
        return 1;
    }

    if (! $response->successful()) {
        $this->error('Failed to fetch currency rates: HTTP ' . $response->status());
        return 1;
    }

    $payload = $response->json();
    $rates = is_array($payload) ? ($payload['rates'] ?? null) : null;

    if (! is_array($rates) || empty($rates)) {
        $this->error('Provider response did not include currency rates.');
        return 1;
    }

    $rates[$base] = 1.0;

    $updated = 0;

    foreach ($rates as $code => $rate) {
        $code = strtoupper((string) $code);

        if ($requestedCodes->isNotEmpty() && ! $requestedCodes->contains($code)) {
            continue;
        }

        if (! is_numeric($rate) || (float) $rate <= 0) {
            continue;
        }

        Currency::updateOrCreate(
            ['code' => $code],
            ['rate' => (float) $rate],
        );

        $updated++;
    }

    $converter->refreshRates();

    $this->info("Updated rates for {$updated} currencies (base {$base}).");

    return 0;
})->purpose('Update currency exchange rates');

Schedule::command('sitemap:warm')->dailyAt('03:00');
