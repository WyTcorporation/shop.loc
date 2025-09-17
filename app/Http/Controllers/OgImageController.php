<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use function formatCurrency;

class OgImageController extends Controller
{
    public function product(string $slug): Response
    {
        $cacheKey = "og:product:{$slug}";
        $png = Cache::remember($cacheKey, now()->addHours(12), function () use ($slug) {
            $p = Product::query()->where('slug', $slug)->first();
            if (!$p) {
                $p = Product::query()->find((int)$slug); // fallback by id
                if (!$p) abort(404);
            }

            $title = (string) $p->name;
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
            $canvas->text('Shop', 660, 80, function ($font) {
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
}
