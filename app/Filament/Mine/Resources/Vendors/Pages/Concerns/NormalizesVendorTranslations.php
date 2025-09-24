<?php

namespace App\Filament\Mine\Resources\Vendors\Pages\Concerns;

use Illuminate\Support\Arr;

trait NormalizesVendorTranslations
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeVendorFormTranslations(array $data): array
    {
        $primaryLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale');
        $supportedLocales = array_values(array_unique(array_filter(
            config('app.supported_locales', [])
        )));

        if ($supportedLocales === []) {
            $supportedLocales = [$primaryLocale];
        } elseif (! in_array($primaryLocale, $supportedLocales, true)) {
            $supportedLocales[] = $primaryLocale;
        }

        $translationAttributes = [
            'name' => 'name_translations',
            'description' => 'description_translations',
        ];

        foreach ($translationAttributes as $attribute => $translationsKey) {
            $raw = $data[$translationsKey] ?? [];
            $translations = [];

            if (is_array($raw)) {
                foreach ($raw as $locale => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $translations[$locale] = $value;
                }
            } elseif ($raw !== null && $raw !== '') {
                $translations[$primaryLocale] = $raw;
            }

            $data[$translationsKey] = $translations;
        }

        foreach ($supportedLocales as $locale) {
            if (! array_key_exists($locale, $data)) {
                continue;
            }

            $localeState = $data[$locale];
            unset($data[$locale]);

            if (is_array($localeState)) {
                foreach ($translationAttributes as $attribute => $translationsKey) {
                    $value = $localeState[$attribute]
                        ?? $localeState["{$attribute}_translations"]
                        ?? null;

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $data[$translationsKey][$locale] = $value;
                }

                continue;
            }

            if ($localeState === null || $localeState === '') {
                continue;
            }

            $data['description_translations'][$locale] = $localeState;
        }

        foreach ($translationAttributes as $attribute => $translationsKey) {
            if (isset($data[$translationsKey])) {
                ksort($data[$translationsKey]);
            }

            if (! blank($data[$attribute] ?? null)) {
                continue;
            }

            $translations = $data[$translationsKey] ?? [];

            if (! is_array($translations) || $translations === []) {
                continue;
            }

            $candidate = $translations[$primaryLocale]
                ?? ($fallbackLocale ? ($translations[$fallbackLocale] ?? null) : null)
                ?? Arr::first($translations);

            if ($candidate !== null && $candidate !== '') {
                $data[$attribute] = $candidate;
            }
        }

        unset($data['translations']);

        return $data;
    }
}
