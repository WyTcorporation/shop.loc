<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Config::set('app.locale', 'en');
    Config::set('app.supported_locales', ['en', 'uk']);
    app()->setLocale('en');
});

it('stores and updates vendor translations from form data', function (): void {
    $owner = User::factory()->create();

    $vendor = Vendor::create([
        'user_id' => $owner->id,
        'name' => 'Acme Vendor',
        'name_translations' => [
            'en' => 'Acme Vendor',
            'uk' => 'Постачальник Acme',
        ],
        'slug' => Str::slug('Acme Vendor'),
        'contact_email' => 'acme@example.com',
        'contact_phone' => '+1234567890',
        'description' => 'English description',
        'description_translations' => [
            'en' => 'English description',
            'uk' => 'Опис українською',
        ],
    ]);

    expect($vendor->name_translations)->toHaveKey('uk', 'Постачальник Acme');
    expect($vendor->description_translations)->toHaveKey('uk', 'Опис українською');
    expect($vendor->name)->toBe('Acme Vendor');
    expect($vendor->description)->toBe('English description');

    $vendor->update([
        'user_id' => $owner->id,
        'name' => 'Updated Vendor',
        'name_translations' => [
            'en' => 'Updated Vendor',
            'uk' => 'Оновлений постачальник',
        ],
        'slug' => Str::slug('Acme Vendor'),
        'contact_email' => 'acme@example.com',
        'contact_phone' => '+1234567890',
        'description' => 'Updated description',
        'description_translations' => [
            'en' => 'Updated description',
            'uk' => 'Оновлений опис',
        ],
    ]);

    $vendor->refresh();

    expect($vendor->name_translations)->toHaveKey('uk', 'Оновлений постачальник');
    expect($vendor->description_translations)->toHaveKey('uk', 'Оновлений опис');
    expect($vendor->name)->toBe('Updated Vendor');
    expect($vendor->description)->toBe('Updated description');
});

it('stores and updates warehouse translations from form data', function (): void {
    $warehouse = Warehouse::create([
        'code' => 'WH-100',
        'name' => 'Main Warehouse',
        'name_translations' => [
            'en' => 'Main Warehouse',
            'uk' => 'Головний склад',
        ],
        'description' => 'Primary storage location',
        'description_translations' => [
            'en' => 'Primary storage location',
            'uk' => 'Основне місце зберігання',
        ],
    ]);

    expect($warehouse->name_translations)->toHaveKey('uk', 'Головний склад');
    expect($warehouse->description_translations)->toHaveKey('uk', 'Основне місце зберігання');
    expect($warehouse->name)->toBe('Main Warehouse');
    expect($warehouse->description)->toBe('Primary storage location');

    $warehouse->update([
        'code' => 'WH-100',
        'name' => 'Regional Warehouse',
        'name_translations' => [
            'en' => 'Regional Warehouse',
            'uk' => 'Регіональний склад',
        ],
        'description' => 'Regional storage hub',
        'description_translations' => [
            'en' => 'Regional storage hub',
            'uk' => 'Регіональний центр зберігання',
        ],
    ]);

    $warehouse->refresh();

    expect($warehouse->name_translations)->toHaveKey('uk', 'Регіональний склад');
    expect($warehouse->description_translations)->toHaveKey('uk', 'Регіональний центр зберігання');
    expect($warehouse->name)->toBe('Regional Warehouse');
    expect($warehouse->description)->toBe('Regional storage hub');
});

it('stores and updates coupon translations from form data', function (): void {
    $coupon = Coupon::create([
        'code' => 'SAVE10',
        'name' => 'Save 10%',
        'name_translations' => [
            'en' => 'Save 10%',
            'uk' => 'Знижка 10%',
        ],
        'description' => 'Save ten percent on your order',
        'description_translations' => [
            'en' => 'Save ten percent on your order',
            'uk' => 'Заощаджуйте десять відсотків на замовленні',
        ],
        'type' => Coupon::TYPE_PERCENT,
        'value' => 10,
        'min_cart_total' => 0,
        'max_discount' => null,
        'usage_limit' => null,
        'per_user_limit' => null,
        'is_active' => true,
    ]);

    expect($coupon->name_translations)->toHaveKey('uk', 'Знижка 10%');
    expect($coupon->description_translations)->toHaveKey('uk', 'Заощаджуйте десять відсотків на замовленні');
    expect($coupon->name)->toBe('Save 10%');
    expect($coupon->description)->toBe('Save ten percent on your order');

    $coupon->update([
        'code' => 'SAVE10',
        'name' => 'Holiday Savings',
        'name_translations' => [
            'en' => 'Holiday Savings',
            'uk' => 'Святкові заощадження',
        ],
        'description' => 'Seasonal promotion',
        'description_translations' => [
            'en' => 'Seasonal promotion',
            'uk' => 'Сезонна акція',
        ],
        'type' => Coupon::TYPE_PERCENT,
        'value' => 15,
        'min_cart_total' => 0,
        'max_discount' => null,
        'usage_limit' => null,
        'per_user_limit' => null,
        'is_active' => true,
    ]);

    $coupon->refresh();

    expect($coupon->name_translations)->toHaveKey('uk', 'Святкові заощадження');
    expect($coupon->description_translations)->toHaveKey('uk', 'Сезонна акція');
    expect($coupon->name)->toBe('Holiday Savings');
    expect($coupon->description)->toBe('Seasonal promotion');
});

it('stores and updates category translations from form data', function (): void {
    $category = Category::create([
        'name' => 'Electronics',
        'name_translations' => [
            'en' => 'Electronics',
            'uk' => 'Електроніка',
        ],
        'slug' => Str::slug('Electronics'),
        'parent_id' => null,
        'is_active' => true,
    ]);

    expect($category->name_translations)->toHaveKey('uk', 'Електроніка');
    expect($category->name)->toBe('Electronics');

    $category->update([
        'name' => 'Accessories',
        'name_translations' => [
            'en' => 'Accessories',
            'uk' => 'Аксесуари',
        ],
        'slug' => Str::slug('Electronics'),
        'parent_id' => null,
        'is_active' => true,
    ]);

    $category->refresh();

    expect($category->name_translations)->toHaveKey('uk', 'Аксесуари');
    expect($category->name)->toBe('Accessories');
});
