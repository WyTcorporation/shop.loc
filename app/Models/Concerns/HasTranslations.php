<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    protected array $translatable = [];

    public function initializeHasTranslations(): void
    {
        foreach ($this->getTranslatableAttributes() as $attribute) {
            $column = $this->getTranslationColumn($attribute);
            if (! array_key_exists($column, $this->casts)) {
                $this->casts[$column] = 'array';
            }
        }
    }

    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            $translations = parent::getAttribute($this->getTranslationColumn($key));

            if (is_array($translations)) {
                $locale = app()->getLocale();
                if ($this->hasTranslationValue($translations, $locale)) {
                    return $translations[$locale];
                }

                $fallback = config('app.fallback_locale');
                if ($fallback && $this->hasTranslationValue($translations, $fallback)) {
                    return $translations[$fallback];
                }
            }
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute($key)) {
            $column = $this->getTranslationColumn($key);
            $translations = parent::getAttribute($column);
            if (! is_array($translations)) {
                $translations = [];
            }

            if (is_array($value)) {
                foreach ($value as $locale => $text) {
                    if ($text === null) {
                        unset($translations[$locale]);
                    } else {
                        $translations[$locale] = $text;
                    }
                }
            } else {
                $translations[app()->getLocale()] = $value;
            }

            parent::setAttribute($column, $translations);

            $baseLocale = config('app.locale');
            $fallbackLocale = config('app.fallback_locale');

            $baseValue = $translations[$baseLocale] ?? null;
            if ($baseValue === null && $fallbackLocale) {
                $baseValue = $translations[$fallbackLocale] ?? null;
            }

            if ($baseValue === null && ! is_array($value)) {
                $baseValue = $value;
            }

            return parent::setAttribute($key, $baseValue);
        }

        return parent::setAttribute($key, $value);
    }

    protected function getTranslationColumn(string $attribute): string
    {
        return $attribute . '_translations';
    }

    protected function isTranslatableAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatableAttributes(), true);
    }

    protected function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    protected function hasTranslationValue(array $translations, string $locale): bool
    {
        return array_key_exists($locale, $translations)
            && $translations[$locale] !== null
            && $translations[$locale] !== '';
    }
}
