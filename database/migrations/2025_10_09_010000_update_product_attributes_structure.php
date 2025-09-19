<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->select(['id', 'attributes'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $normalized = $this->normalizeAttributes($row->attributes);
                    DB::table('products')
                        ->where('id', $row->id)
                        ->update(['attributes' => $normalized]);
                }
            });
    }

    public function down(): void
    {
        DB::table('products')
            ->select(['id', 'attributes'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $legacy = $this->legacyAttributes($row->attributes);
                    DB::table('products')
                        ->where('id', $row->id)
                        ->update(['attributes' => $legacy]);
                }
            });
    }

    private function normalizeAttributes($raw): array
    {
        $supported = config('app.supported_locales', [config('app.locale')]);
        $defaultLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);

        $items = $this->decode($raw);
        if (empty($items)) {
            return [];
        }

        $normalized = [];

        if (!Arr::isAssoc($items) && isset($items[0]) && is_array($items[0]) && array_key_exists('key', $items[0]) && array_key_exists('value', $items[0])) {
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['key'], $item['value'])) {
                    continue;
                }
                $translations = $this->prepareTranslations($item['translations'] ?? [], $item['value'], $supported, $defaultLocale, $fallbackLocale);
                $normalized[] = [
                    'key' => (string) $item['key'],
                    'value' => (string) $item['value'],
                    'translations' => $translations,
                ];
            }

            return $normalized;
        }

        foreach ($items as $key => $value) {
            if (is_numeric($key) && is_array($value) && isset($value['key'], $value['value'])) {
                $translations = $this->prepareTranslations($value['translations'] ?? [], $value['value'], $supported, $defaultLocale, $fallbackLocale);
                $normalized[] = [
                    'key' => (string) $value['key'],
                    'value' => (string) $value['value'],
                    'translations' => $translations,
                ];
                continue;
            }

            $valueData = [
                'value' => is_array($value) && array_key_exists('value', $value) ? $value['value'] : $value,
                'translations' => is_array($value) && array_key_exists('translations', $value) ? (array) $value['translations'] : [],
            ];

            $machine = $this->machineValue($valueData['value']);
            $translations = $this->prepareTranslations($valueData['translations'], $valueData['value'], $supported, $defaultLocale, $fallbackLocale);

            $normalized[] = [
                'key' => (string) $key,
                'value' => $machine,
                'translations' => $translations,
            ];
        }

        return $normalized;
    }

    private function legacyAttributes($raw): array
    {
        $items = $this->decode($raw);
        if (empty($items)) {
            return [];
        }

        if (!Arr::isAssoc($items) && isset($items[0]) && is_array($items[0]) && array_key_exists('key', $items[0]) && array_key_exists('value', $items[0])) {
            $legacy = [];
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['key'], $item['value'])) {
                    continue;
                }
                $translations = (array) ($item['translations'] ?? []);
                $legacy[$item['key']] = $translations ?: $item['value'];
            }

            return $legacy;
        }

        return (array) $items;
    }

    private function decode($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (array) $decoded;
            }
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return [];
    }

    private function prepareTranslations($translations, $fallbackValue, array $locales, string $defaultLocale, string $fallbackLocale): array
    {
        $translations = array_filter((array) $translations, fn ($value) => is_string($value) && $value !== '');
        $fallbackValue = is_string($fallbackValue) ? $fallbackValue : null;

        if ($fallbackValue !== null) {
            $translations += [$defaultLocale => $fallbackValue];
        }

        if ($fallbackValue !== null && $fallbackLocale) {
            $translations += [$fallbackLocale => $translations[$fallbackLocale] ?? $fallbackValue];
        }

        foreach ($locales as $locale) {
            if (!isset($translations[$locale]) && $fallbackValue !== null) {
                $translations[$locale] = $fallbackValue;
            }
        }

        return $translations;
    }

    private function machineValue($value): string
    {
        $value = is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = substr(sha1($value), 0, 12);
        }

        return $slug ?: 'value';
    }
};
