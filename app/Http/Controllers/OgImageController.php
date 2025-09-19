<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use function formatCurrency;

class OgImageController extends Controller
{
    public function product(string $slug): Response
    {
        $locale = app()->getLocale();
        $cacheKey = "og:product:{$locale}:{$slug}";
        $png = Cache::remember($cacheKey, now()->addHours(12), function () use ($slug, $locale) {
            $p = Product::query()->where('slug', $slug)->first();
            if (!$p) {
                $p = Product::query()->find((int)$slug); // fallback by id
                if (!$p) abort(404);
            }

            $fallbackLocale = config('app.fallback_locale');
            $title = $this->resolveLocalizedValue($p->name, $locale, $fallbackLocale);
            $price = formatCurrency($p->price);
            $imgUrl = $p->preview_url ?? optional($p->images()->orderByDesc('is_primary')->orderBy('id')->first())->url;

            $manager = new ImageManager(new Driver());
            $canvas = $manager->create(1200, 630)->fill('#f7f7f7');

            // фонова картинка товару (якщо є)
            if ($imgUrl) {
                try {
                    $photo = $manager->read($imgUrl)->cover(630, 630);
                    $canvas->place($photo, 'top-left', 0, 0);
                } catch (\Throwable $e) {
                    // ігноруємо, якщо недоступна
                }
            }

            // правий блок
            // простий текст без кастомних шрифтів (щоб не возитись з файлами)
            $brand = (string) (config('app.name') ?: __('shop.meta.brand', [], $locale));
            $canvas->text($brand, 660, 80, function ($font) {
                $font->size(36);
                $font->color('#111111');
            });
            $canvas->text($title, 660, 160, function ($font) {
                $font->size(44);
                $font->color('#111111');
                $font->lineHeight(1.2);
            });
            $canvas->text($price, 660, 260, function ($font) {
                $font->size(40);
                $font->color('#0ea5e9');
            });

            return (string)$canvas->toPng();
        });

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    private function resolveLocalizedValue(mixed $value, string $locale, ?string $fallbackLocale): string
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return trim($value);
            }
        }

        if (is_array($value)) {
            foreach ([$locale, $fallbackLocale] as $lang) {
                if ($lang === null) {
                    continue;
                }

                if (array_key_exists($lang, $value) && $value[$lang] !== null && $value[$lang] !== '') {
                    return trim((string) $value[$lang]);
                }
            }

            foreach ($value as $item) {
                if ($item !== null && $item !== '') {
                    return trim((string) $item);
                }
            }

            return '';
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value);
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return '';
    }
}
