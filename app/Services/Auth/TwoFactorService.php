<?php

namespace App\Services\Auth;

use App\Models\User;

class TwoFactorService
{
    private const SECRET_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const OTP_DIGITS = 6;
    private const OTP_PERIOD = 30;

    public function generateSecret(int $length = 32): string
    {
        $alphabet = str_split(self::SECRET_ALPHABET);
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, count($alphabet) - 1)];
        }

        return $secret;
    }

    public function makeOtpAuthUrl(User $user, string $secret): string
    {
        $issuer = rawurlencode(config('app.name', 'Shop'));
        $labelSource = $user->email ?: ($user->name ?: ('user-'.$user->getKey()));
        $label = rawurlencode($labelSource);

        $query = http_build_query([
            'secret' => $secret,
            'issuer' => config('app.name', 'Shop'),
            'digits' => self::OTP_DIGITS,
            'period' => self::OTP_PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$issuer}:{$label}?{$query}";
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $normalizedSecret = strtoupper($secret);
        $normalizedCode = preg_replace('/[^0-9]/', '', (string) $code);

        if ($normalizedCode === '') {
            return false;
        }

        $normalizedCode = str_pad(
            substr($normalizedCode, -self::OTP_DIGITS),
            self::OTP_DIGITS,
            '0',
            STR_PAD_LEFT
        );

        $timestamp = time();

        for ($offset = -$window; $offset <= $window; $offset++) {
            $candidate = $this->generateCodeForTimestamp($normalizedSecret, $timestamp + ($offset * self::OTP_PERIOD));

            if ($candidate !== null && hash_equals($candidate, $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    public function getCurrentCode(string $secret, ?int $timestamp = null): ?string
    {
        return $this->generateCodeForTimestamp(strtoupper($secret), $timestamp ?? time());
    }

    private function generateCodeForTimestamp(string $secret, int $timestamp): ?string
    {
        $binarySecret = $this->base32Decode($secret);

        if ($binarySecret === null || $binarySecret === '') {
            return null;
        }

        $counter = (int) floor($timestamp / self::OTP_PERIOD);
        $counterBytes = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $counterBytes, $binarySecret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;

        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binary % (10 ** self::OTP_DIGITS);

        return str_pad((string) $otp, self::OTP_DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): ?string
    {
        $cleanSecret = preg_replace('/[^A-Z2-7]/', '', $secret);

        if ($cleanSecret === '') {
            return null;
        }

        $alphabet = array_flip(str_split(self::SECRET_ALPHABET));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($cleanSecret) as $char) {
            if (! isset($alphabet[$char])) {
                return null;
            }

            $buffer = ($buffer << 5) | $alphabet[$char];
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
