<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetLocaleFromRequest
{
    /** @var string[] */
    private array $supported = ['uk', 'en'];
    private string $fallback = 'uk';

    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        // 1) prefix /{locale}/...
        $seg1 = trim($request->segment(1) ?? '', '/');
        if (in_array($seg1, $this->supported, true)) {
            $locale = $seg1;
        }

        // 2) cookie "lang"
        if (!$locale) {
            $cookie = (string)$request->cookie('lang');
            if (in_array($cookie, $this->supported, true)) {
                $locale = $cookie;
            }
        }

        // 3) Accept-Language
        if (!$locale) {
            $al = (string)$request->header('Accept-Language', '');
            $first = Str::of($al)->explode(',')->first();
            $first = Str::of((string)$first)->before(';')->lower()->value();
            if (in_array($first, $this->supported, true)) {
                $locale = $first;
            } elseif (in_array(Str::substr($first, 0, 2), $this->supported, true)) {
                $locale = Str::substr($first, 0, 2);
            }
        }

        app()->setLocale($locale ?: $this->fallback);

        return $next($request);
    }
}
