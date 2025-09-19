<?php


use App\Http\Middleware\SetLocaleFromRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

beforeEach(function () {
    Product::factory()->count(5)->create();
});

it('lists products', function () {
    $this->getJson('/api/products')->assertOk()->assertJsonStructure([
        'data', 'current_page', 'per_page'
    ]);
});

it('filters products by search', function () {
    $this->getJson('/api/products?search=est')->assertOk();
});

it('returns localized descriptions for each supported locale', function () {
    $locales = config('app.supported_locales');
    $defaultLocale = config('app.locale');

    $this->withoutMiddleware(SetLocaleFromRequest::class);

    $nameTranslations = [
        'uk' => 'Тестовий товар',
        'en' => 'Test product',
        'ru' => 'Тестовый товар',
        'pt' => 'Produto de teste',
    ];

    $descriptionTranslations = [
        'uk' => 'Опис українською',
        'en' => 'Description in English',
        'ru' => 'Описание на русском',
        'pt' => 'Descrição em português',
    ];

    $product = Product::factory()->create([
        'name' => $nameTranslations[$defaultLocale] ?? reset($nameTranslations),
        'name_translations' => $nameTranslations,
        'description' => $descriptionTranslations[$defaultLocale] ?? reset($descriptionTranslations),
        'description_translations' => $descriptionTranslations,
        'slug' => 'localized-product-test',
        'is_active' => true,
    ]);

    expect($product->fresh()->description_translations)->toMatchArray($descriptionTranslations);
    expect($product->fresh()->description)->toBe($descriptionTranslations[$defaultLocale]);

    foreach ($locales as $locale) {
        app()->setLocale($locale);

        $this->getJson("/api/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('description', $descriptionTranslations[$locale])
            ->assertJsonPath("description_translations.{$locale}", $descriptionTranslations[$locale]);
    }
});

it('creates order from cart flow (smoke)', function () {
    $product = Product::factory()->create([
        'price' => 12.34,
        'stock' => 5,
    ]);

    $cart = Cart::factory()->create(); // UUID

    CartItem::factory()->create([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'qty'        => 2,
        'price'      => $product->price,
    ]);

    $payload = [
        'cart_id' => $cart->id,
        'email'   => 'customer@example.com',
        'shipping_address' => ['name' => 'John', 'city' => 'Kyiv', 'addr' => 'Street 1'],
    ];

    $this->postJson('/api/orders', $payload)
        ->assertCreated()
        ->assertJsonPath('items.0.product_id', $product->id)
        ->assertJsonPath('shipment.status', 'pending');
});
