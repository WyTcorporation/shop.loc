<?php

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

Schedule::command('sitemap:warm')->dailyAt('03:00');
