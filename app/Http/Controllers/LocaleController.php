<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocaleController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $supported = collect(config('app.supported_locales', []))
            ->map(function ($locale) {
                return Str::of((string) $locale)
                    ->lower()
                    ->replace('_', '-')
                    ->value();
            })
            ->filter()
            ->unique()
            ->values();

        $requested = Str::of((string) $request->input('locale'))
            ->lower()
            ->replace('_', '-')
            ->value();

        if (! $supported->contains($requested)) {
            $requested = $supported->first() ?? Str::of((string) config('app.locale'))
                ->lower()
                ->replace('_', '-')
                ->value();
        }

        $redirectTo = $this->resolveRedirectUrl($request);

        $cookie = cookie(
            'lang',
            $requested,
            60 * 24 * 365,
            '/',
            config('session.domain'),
            config('session.secure', false),
            false,
            false,
            'lax',
        );

        return redirect()->to($redirectTo)->withCookie($cookie);
    }

    private function resolveRedirectUrl(Request $request): string
    {
        $candidates = [
            (string) $request->input('redirect', ''),
            url()->previous() ?: '',
        ];

        $host = $request->getHost();

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (Str::startsWith($candidate, ['/'])) {
                return $candidate;
            }

            if (! Str::startsWith($candidate, ['http://', 'https://'])) {
                continue;
            }

            $candidateHost = (string) parse_url($candidate, PHP_URL_HOST);

            if ($candidateHost !== '' && $candidateHost === $host) {
                return $candidate;
            }
        }

        return url('/mine');
    }
}
