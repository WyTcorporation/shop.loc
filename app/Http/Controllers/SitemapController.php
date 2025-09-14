<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Кешуй індекс (10 хв. — під себе)
        $xml = Cache::remember('sitemap:index', now()->addMinutes(10), function () {
            $host = URL::to('/');
            $perPage = 5000;

            $total = (int) Product::query()->where('is_active', true)->count();
            $pages = max(1, (int) ceil($total / $perPage));

            $now = Carbon::now()->toAtomString();

            $items = [];
            // categories
            $items[] = [
                'loc' => "{$host}/sitemaps/categories.xml",
                'lastmod' => $now,
            ];
            // products pages
            for ($i = 1; $i <= $pages; $i++) {
                $items[] = [
                    'loc' => "{$host}/sitemaps/products-{$i}.xml",
                    'lastmod' => $now,
                ];
            }

            $out = [];
            $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $out[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($items as $it) {
                $out[] = '  <sitemap>';
                $out[] = "    <loc>{$it['loc']}</loc>";
                $out[] = "    <lastmod>{$it['lastmod']}</lastmod>";
                $out[] = '  </sitemap>';
            }
            $out[] = '</sitemapindex>';
            return implode("\n", $out);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function categories(): Response
    {
        $xml = Cache::remember('sitemap:categories', now()->addMinutes(10), function () {
            $host = URL::to('/');

            $rows = Category::query()
                ->select(['id', 'updated_at'])
                ->orderBy('id')
                ->get();

            $out = [];
            $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Головна (каталог)
            $out[] = '  <url>';
            $out[] = "    <loc>{$host}/</loc>";
            $out[] = '    <changefreq>daily</changefreq>';
            $out[] = '    <priority>1.0</priority>';
            $out[] = '  </url>';

            foreach ($rows as $c) {
                $loc = "{$host}/?category_id={$c->id}";
                $lastmod = optional($c->updated_at)->toAtomString() ?? Carbon::now()->toAtomString();

                $out[] = '  <url>';
                $out[] = "    <loc>{$loc}</loc>";
                $out[] = "    <lastmod>{$lastmod}</lastmod>";
                $out[] = '    <changefreq>weekly</changefreq>';
                $out[] = '  </url>';
            }

            $out[] = '</urlset>';
            return implode("\n", $out);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function products(int $page): Response
    {
        $perPage = 5000;
        $page = max(1, $page);
        $cacheKey = "sitemap:products:{$page}";

        $xml = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($page, $perPage) {
            $host = URL::to('/');

            $rows = Product::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get(['slug', 'id', 'updated_at']);

            $out = [];
            $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($rows as $p) {
                $slug = $p->slug ?: $p->id;
                $loc = "{$host}/product/{$slug}";
                $lastmod = optional($p->updated_at)->toAtomString();

                $out[] = '  <url>';
                $out[] = "    <loc>{$loc}</loc>";
                if ($lastmod) $out[] = "    <lastmod>{$lastmod}</lastmod>";
                $out[] = '    <changefreq>weekly</changefreq>';
                $out[] = '    <priority>0.8</priority>';
                $out[] = '  </url>';
            }

            $out[] = '</urlset>';
            return implode("\n", $out);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
