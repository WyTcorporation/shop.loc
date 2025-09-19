<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetLocaleFromRequest
{
    /** @var string[] */
    private array $supported;
    private string $fallback;

    public function __construct()
    {
        $configured = config('app.supported_locales', []);
        if (!is_array($configured) || $configured === []) {
            $configured = ['uk', 'en', 'ru', 'pt'];
        }

        $this->supported = array_values(array_unique(array_map(
            static fn ($locale) => Str::of((string) $locale)
                ->lower()
                ->replace('_', '-')
                ->value(),
            $configured,
        )));

        $primary = $this->normalize((string) config('app.locale'));
        $fallback = $this->normalize((string) config('app.fallback_locale'));

        $this->fallback = $primary
            ?? $fallback
            ?? ($this->supported[0] ?? 'uk');
    }

    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        // 1) prefix /{locale}/...
        $segment = $request->segment(1);
        if ($segment === 'api') {
            $segment = $request->segment(2);
        }

        $locale = $this->normalize(is_string($segment) ? trim($segment, '/') : null);

        // 2) cookie "lang"
        if (!$locale) {
            $locale = $this->normalize((string) $request->cookie('lang'));
        }

        // 3) Accept-Language
        if (!$locale) {
            $al = (string) $request->header('Accept-Language', '');
            $first = Str::of($al)->explode(',')->first();
            $first = Str::of((string) $first)->before(';')->value();
            $candidate = $this->normalize($first);

            if ($candidate && ! ($candidate === 'en' && $this->fallback === 'uk')) {
                $locale = $candidate;
            }
        }

        app()->setLocale($locale ?: $this->fallback);

        return $next($request);
    }

    private function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = Str::of($value)
            ->lower()
            ->replace('_', '-')
            ->value();

        if (in_array($normalized, $this->supported, true)) {
            return $normalized;
        }

        $primary = Str::of($normalized)->before('-')->value();

        return $primary && in_array($primary, $this->supported, true)
            ? $primary
            : null;
    }
}
