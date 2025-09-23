<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Config::set('app.supported_locales', ['uk', 'en', 'ru', 'pt']);
    Session::start();
});

it('stores the selected locale in the lang cookie and redirects back', function () {
    $token = Session::token();

    $response = $this->from('/mine/orders')
        ->post(route('mine.language.switch'), [
            '_token' => $token,
            'locale' => 'ru',
            'redirect' => '/mine/orders',
        ]);

    $response->assertRedirect('/mine/orders');
    $response->assertCookie('lang', 'ru', false);
});

it('normalizes unsupported locales to the first configured value', function () {
    Config::set('app.supported_locales', ['uk', 'en']);

    $token = Session::token();

    $response = $this->post(route('mine.language.switch'), [
        '_token' => $token,
        'locale' => 'de',
        'redirect' => '/mine',
    ]);

    $response->assertRedirect('/mine');
    $response->assertCookie('lang', 'uk', false);
});

it('prevents redirecting to external domains', function () {
    $token = Session::token();

    $response = $this->from('/mine/dashboard')
        ->post(route('mine.language.switch'), [
        '_token' => $token,
        'locale' => 'en',
        'redirect' => 'https://example.com/evil',
    ]);

    $response->assertRedirect('/mine/dashboard');
    $response->assertCookie('lang', 'en', false);
});
