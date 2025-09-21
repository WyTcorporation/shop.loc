<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Controllers\SitemapController;

Route::get('/og/product/{slug}.png', [OgImageController::class, 'product'])->where('slug', '.*');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemaps/categories.xml', [SitemapController::class, 'categories']);
Route::get('/sitemaps/products-{page}.xml', [SitemapController::class, 'products'])
    ->whereNumber('page');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Sitemap: ' . url('/sitemap.xml'),
    ];
    return response(implode(PHP_EOL, $lines), 200)
        ->header('Content-Type', 'text/plain');
});

Route::post('/mine/language', LocaleController::class)
    ->middleware(['web'])
    ->name('mine.language.switch');

Route::view('{any}', 'shop')->where('any', '^(?!mine|api).*$');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
