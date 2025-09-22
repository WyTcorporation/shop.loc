<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

it('returns localized order shipment subject for each supported locale', function (string $locale, string $expected) {
    App::setLocale($locale);

    expect(__('shop.orders.shipped.subject'))
        ->toBe($expected);
})->with([
    ['en', 'Order on the way'],
    ['uk', 'Замовлення в дорозі'],
    ['ru', 'Заказ в пути'],
    ['pt', 'Pedido a caminho'],
]);

it('falls back to english when locale has no translation', function () {
    $originalFallback = App::getFallbackLocale();

    Config::set('app.fallback_locale', 'en');
    App::setFallbackLocale('en');
    App::setLocale('fr');

    expect(__('shop.orders.shipped.subject'))
        ->toBe('Order on the way');

    App::setFallbackLocale($originalFallback);
    Config::set('app.fallback_locale', $originalFallback);
});
