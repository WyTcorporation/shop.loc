<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Config::set('app.supported_locales', ['uk', 'en', 'ru', 'pt']);
    Config::set('app.fallback_locale', 'uk');
    App::setLocale('uk');

    if (! Route::has('testing.locale.api.simple')) {
        Route::prefix('api')->middleware('api')->group(function () {
            Route::get('/test-locale', fn () => response()->json([
                'locale' => app()->getLocale(),
                'fallback' => config('app.fallback_locale'),
            ]))
                ->name('testing.locale.api.simple');

            Route::get('/{locale}/test-locale', fn () => response()->json([
                'locale' => app()->getLocale(),
                'fallback' => config('app.fallback_locale'),
            ]))
                ->name('testing.locale.api.prefixed');
        });
    }
});

it('sets locale from url prefix including normalized variants', function (string $path, string $expected) {
    $response = $this->getJson($path);

    $response->assertOk();
    expect($response->json('locale'))->toBe($expected);
    expect(App::getLocale())->toBe($expected);
})->with([
    ['/api/ru/test-locale', 'ru'],
    ['/api/pt-BR/test-locale', 'pt'],
]);

it('sets locale from lang cookie with normalization', function (string $cookie, string $expected) {
    $response = $this->withCredentials()->withCookie('lang', $cookie)->getJson('/api/test-locale');

    $response->assertOk();
    expect($response->json('locale'))->toBe($expected);
    expect(App::getLocale())->toBe($expected);
})->with([
    ['RU', 'ru'],
    ['pt_BR', 'pt'],
]);

it('sets locale from accept language header with normalization', function (string $header, string $expected) {
    $response = $this->getJson('/api/test-locale', ['Accept-Language' => $header]);

    $response->assertOk();
    expect($response->json('locale'))->toBe($expected);
    expect(App::getLocale())->toBe($expected);
})->with([
    ['ru-RU,ru;q=0.8,en;q=0.5', 'ru'],
    ['pt-BR,pt;q=0.9,en;q=0.8', 'pt'],
]);
