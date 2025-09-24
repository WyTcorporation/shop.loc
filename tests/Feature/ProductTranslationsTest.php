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
        'description' => 'Опис ноутбука',
        'description_translations' => [
            'uk' => 'Опис ноутбука',
            'en' => 'Laptop description',
        ],
    ]);

    app()->setLocale('uk');
    expect($product->fresh()->name)->toBe('Ноутбук');
    expect($product->fresh()->description)->toBe('Опис ноутбука');

    app()->setLocale('en');
    expect($product->fresh()->name)->toBe('Laptop');
    expect($product->fresh()->description)->toBe('Laptop description');

    app()->setLocale('pl');
    expect($product->fresh()->name)->toBe('Laptop');
    expect($product->fresh()->description)->toBe('Laptop description');
});

it('hydrates the legacy name column from available translations when blank', function () {
    $product = Product::factory()->create([
        'name' => null,
        'name_translations' => [
            'uk' => 'Ноутбук',
            'en' => 'Laptop',
        ],
        'description' => 'Опис ноутбука',
        'description_translations' => [
            'uk' => 'Опис ноутбука',
            'en' => 'Laptop description',
        ],
    ]);

    expect($product->getRawOriginal('name'))->toBe('Ноутбук');
    expect($product->fresh()->name)->toBe('Ноутбук');
});

it('falls back to legacy attribute when translations are missing', function () {
    $product = Product::factory()->create([
        'name' => 'Стара назва',
        'name_translations' => null,
        'description' => 'Старий опис',
        'description_translations' => null,
    ]);

    app()->setLocale('en');

    expect($product->fresh()->name)->toBe('Стара назва');
    expect($product->fresh()->description)->toBe('Старий опис');
});

it('saves translations for the current locale when assigning attributes', function () {
    $product = Product::factory()->create([
        'name' => 'Ноутбук',
        'name_translations' => [
            'uk' => 'Ноутбук',
        ],
        'description' => 'Базовий опис',
        'description_translations' => [
            'uk' => 'Базовий опис',
        ],
    ]);

    app()->setLocale('en');
    $product->name = 'Laptop';
    $product->description = 'Laptop description';
    $product->save();

    $product->refresh();

    expect($product->name_translations)->toHaveKey('en', 'Laptop');
    expect($product->description_translations)->toHaveKey('en', 'Laptop description');

    app()->setLocale('en');
    expect($product->fresh()->name)->toBe('Laptop');
    expect($product->fresh()->description)->toBe('Laptop description');

    app()->setLocale('uk');
    expect($product->fresh()->name)->toBe('Ноутбук');
    expect($product->fresh()->description)->toBe('Базовий опис');
});
