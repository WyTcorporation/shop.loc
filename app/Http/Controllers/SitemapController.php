<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = config('app.url');

        $static = [
            ['loc' => $base . '/', 'changefreq' => 'daily',  'priority' => '1.0'],
            // Додай інші SEO-сторінки, якщо є
        ];

        $products = Product::query()
            ->where('is_active', true)
            ->select(['slug','updated_at'])
            ->orderByDesc('id')
            ->take(5000) // ліміт на випадок великих каталогів
            ->get();

        // Якщо немає окремих сторінок категорій — можна пропустити або зробити UTM/параметри
        $categories = Category::query()
            ->select(['id','slug','updated_at'])
            ->orderBy('id')
            ->get();

        $xml = view('sitemap', compact('base','static','products','categories'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
