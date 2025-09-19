<?php

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Cache::flush();
    app()->setLocale(config('app.locale'));
    Config::set('app.fallback_locale', 'en');
});

it('returns categories from cache', function () {
    Category::factory()->create([
        'name' => 'Database Category',
        'slug' => 'database-category',
    ]);

    $cached = [
        [
            'id' => 999,
            'name' => 'Cached Category',
            'slug' => 'cached-category',
            'parent_id' => null,
        ],
    ];

    Cache::put(Category::CACHE_KEY_FLAT, $cached, now()->addMinutes(10));

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertExactJson($cached);
});

it('clears cached categories on changes', function () {
    Cache::put(Category::CACHE_KEY_FLAT, [['id' => 1]], now()->addMinutes(10));
    Cache::put(Category::CACHE_KEY_TREE, [['id' => 1]], now()->addMinutes(10));

    Category::factory()->create();

    expect(Cache::has(Category::CACHE_KEY_FLAT))->toBeFalse();
    expect(Cache::has(Category::CACHE_KEY_TREE))->toBeFalse();
});

it('returns localized category names for different locales from cache', function () {
    $category = Category::factory()->create([
        'name' => 'Категорії',
        'name_translations' => [
            'uk' => 'Категорії',
            'en' => 'Categories',
        ],
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('0.name', 'Категорії');

    app()->setLocale('en');

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('0.name', 'Categories');
});
