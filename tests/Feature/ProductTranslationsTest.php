<?php

use App\Models\Product;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    app()->setLocale(config('app.locale'));
    Config::set('app.fallback_locale', 'en');
});

it('returns localized names with fallback to configured fallback locale', function () {
    $product = Product::factory()->create([
        'name' => 'Ноутбук',
        'name_translations' => [
            'uk' => 'Ноутбук',
            'en' => 'Laptop',
        ],
    ]);

    app()->setLocale('uk');
    expect($product->fresh()->name)->toBe('Ноутбук');

    app()->setLocale('en');
    expect($product->fresh()->name)->toBe('Laptop');

    app()->setLocale('pl');
    expect($product->fresh()->name)->toBe('Laptop');
});

it('falls back to legacy attribute when translations are missing', function () {
    $product = Product::factory()->create([
        'name' => 'Стара назва',
        'name_translations' => null,
    ]);

    app()->setLocale('en');

    expect($product->fresh()->name)->toBe('Стара назва');
});

it('saves translations for the current locale when assigning attributes', function () {
    $product = Product::factory()->create([
        'name' => 'Ноутбук',
        'name_translations' => [
            'uk' => 'Ноутбук',
        ],
    ]);

    app()->setLocale('en');
    $product->name = 'Laptop';
    $product->save();

    $product->refresh();

    expect($product->name_translations)->toHaveKey('en', 'Laptop');

    app()->setLocale('en');
    expect($product->fresh()->name)->toBe('Laptop');

    app()->setLocale('uk');
    expect($product->fresh()->name)->toBe('Ноутбук');
});
