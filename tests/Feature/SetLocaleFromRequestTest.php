<?php

use App\Http\Middleware\SetLocaleFromRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('app.supported_locales', ['uk', 'en', 'ru', 'pt']);
    Config::set('app.fallback_locale', 'uk');
    App::setLocale('uk');
});

it('sets locale from url prefix including normalized variants', function (string $path, string $expected) {
    $request = Request::create($path, 'GET');
    $middleware = new SetLocaleFromRequest();

    $middleware->handle($request, fn () => null);

    expect(App::getLocale())->toBe($expected);
})->with([
    ['/ru/products', 'ru'],
    ['/pt-BR/catalog', 'pt'],
]);

it('sets locale from lang cookie with normalization', function (string $cookie, string $expected) {
    $request = Request::create('/', 'GET');
    $request->cookies->set('lang', $cookie);
    $middleware = new SetLocaleFromRequest();

    $middleware->handle($request, fn () => null);

    expect(App::getLocale())->toBe($expected);
})->with([
    ['RU', 'ru'],
    ['pt_BR', 'pt'],
]);

it('sets locale from accept language header with normalization', function (string $header, string $expected) {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT_LANGUAGE' => $header]);
    $middleware = new SetLocaleFromRequest();

    $middleware->handle($request, fn () => null);

    expect(App::getLocale())->toBe($expected);
})->with([
    ['ru-RU,ru;q=0.8,en;q=0.5', 'ru'],
    ['pt-BR,pt;q=0.9,en;q=0.8', 'pt'],
]);
