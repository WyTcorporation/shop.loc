<?php

namespace Database\Seeders\Concerns;

trait GeneratesLocalizedText
{
    /**
     * @param  array<string,string>  $translations
     * @return array{value: string, translations: array<string,string>}
     */
    protected function localized(array $translations): array
    {
        $default = config('app.locale');
        $value = $translations[$default] ?? reset($translations);

        return [
            'value' => $value,
            'translations' => $translations,
        ];
    }
}
