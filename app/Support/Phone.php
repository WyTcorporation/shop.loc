<?php

namespace App\Support;

use Stringable;

final class Phone
{
    private const MAX_DIGITS = 15;

    public static function format(null|string|int|float|Stringable $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_int($value)) {
            $value = (string) $value;
        } elseif (is_float($value)) {
            $value = rtrim(rtrim(sprintf('%.0f', $value), '0'), '.');
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $sanitized = preg_replace('/[^+\d]/', '', $trimmed) ?? '';
        if ($sanitized === '') {
            return '';
        }

        $sanitized = preg_replace('/(?!^)\+/', '', $sanitized) ?? '';
        if ($sanitized === '') {
            return '';
        }

        if (! str_starts_with($sanitized, '+')) {
            $sanitized = '+' . ltrim($sanitized, '+');
        }

        $digits = substr(preg_replace('/\D/', '', $sanitized) ?? '', 0, self::MAX_DIGITS);

        if ($digits === '') {
            return '+';
        }

        $chunks = str_split($digits, 3);

        return '+' . implode(' ', $chunks);
    }

    public static function normalize(null|string|int|float|Stringable $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $formatted = self::format($value);

        if ($formatted === null || $formatted === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $formatted) ?? '';

        if ($digits === '') {
            return null;
        }

        return '+' . substr($digits, 0, self::MAX_DIGITS);
    }
}
