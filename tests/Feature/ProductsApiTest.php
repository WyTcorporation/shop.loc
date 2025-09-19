<?php


use App\Http\Middleware\SetLocaleFromRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakePgsqlConnectionResolver;
use Tests\Support\FakePgsqlSqliteConnection;
use Database\Support\TranslationGenerator;

beforeEach(function () {
    Product::factory()->count(5)->create();
});

it('lists products', function () {
    $this->getJson('/api/products')->assertOk()->assertJsonStructure([
        'data', 'current_page', 'per_page'
    ]);
});

it('filters products by search', function () {
    config()->set('scout.driver', 'database');
    config()->set('app.locale', 'en');
    config()->set('app.fallback_locale', 'en');

    $this->withoutMiddleware(SetLocaleFromRequest::class);

    Product::query()->delete();

    $product = Product::factory()->create([
        'is_active' => true,
        'name' => 'Laptop',
        'name_translations' => [
            'en' => 'Laptop',
            'pt' => 'Computador',
        ],
    ]);

    app()->setLocale('pt');

    $response = $this->getJson('/api/products?search=Computador', ['Accept-Language' => 'pt']);

    $response->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', 'Computador');
});

it('falls back to postgres json search without mysql functions', function () {
    config()->set('scout.driver', 'database');
    config()->set('app.locale', 'ru');
    config()->set('app.fallback_locale', 'en');
    app()->setLocale('ru');

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $connection = new FakePgsqlSqliteConnection($pdo, ':memory:', '', ['driver' => 'pgsql']);

    /** @var ConnectionResolverInterface $originalResolver */
    $originalResolver = Model::getConnectionResolver();
    Model::setConnectionResolver(new FakePgsqlConnectionResolver($connection));

    DB::shouldReceive('connection')->andReturn($connection);

    $schema = $connection->getSchemaBuilder();
    $schema->create('products', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->json('name_translations')->nullable();
        $table->string('slug')->default('');
        $table->string('sku')->default('');
        $table->boolean('is_active')->default(true);
        $table->integer('stock')->default(0);
        $table->decimal('price', 10, 2)->nullable();
        $table->integer('price_cents')->nullable();
        $table->timestamps();
    });

    $schema->create('currencies', function (Blueprint $table) {
        $table->increments('id');
        $table->string('code');
        $table->decimal('rate', 12, 6)->default(1);
    });

    $schema->create('product_images', function (Blueprint $table) {
        $table->increments('id');
        $table->unsignedInteger('product_id');
        $table->string('disk')->nullable();
        $table->string('path')->nullable();
        $table->json('alt_translations')->nullable();
        $table->boolean('is_primary')->default(false);
        $table->integer('sort')->default(0);
    });

    $schema->create('vendors', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name')->nullable();
        $table->string('slug')->default('');
        $table->string('contact_email')->nullable();
        $table->string('contact_phone')->nullable();
    });

    $connection->flushCapturedQueries();

    try {
        Product::query()->insert([
            [
                'name' => 'Base One',
                'name_translations' => json_encode(['ru' => 'Ananas']),
                'slug' => 'base-one',
                'sku' => 'SKU-1',
                'stock' => 5,
                'price' => 12.50,
                'price_cents' => 1250,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/products?search=Ananas&per_page=10')->assertOk();

        $response->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.name', 'Ananas');

        $captured = $connection->capturedQueries();

        expect($captured)->not()->toBeEmpty();
        expect(collect($captured)->contains(fn ($sql) => str_contains($sql, 'jsonb_each_text') && str_contains($sql, 'ILIKE')))
            ->toBeTrue();
        expect(collect($captured)->contains(fn ($sql) => str_contains($sql, 'JSON_UNQUOTE')))
            ->toBeFalse();
    } finally {
        $schema->dropIfExists('vendors');
        $schema->dropIfExists('product_images');
        $schema->dropIfExists('currencies');
        $schema->dropIfExists('products');

        Model::setConnectionResolver($originalResolver);
    }
});

it('filters products by machine attribute value and returns localized attributes', function () {
    config()->set('scout.driver', 'collection');
    Product::query()->delete();

    $black = TranslationGenerator::attributeOption('color', 'black');
    $sizeM = TranslationGenerator::attributeOption('size', 'm');
    $red = TranslationGenerator::attributeOption('color', 'red');

    $product = Product::factory()->create([
        'is_active' => true,
        'name' => 'Localized filter product',
        'attributes' => [$black, $sizeM],
    ]);

    Product::factory()->create([
        'is_active' => true,
        'attributes' => [$red, TranslationGenerator::attributeOption('size', 'l')],
    ]);

    app()->setLocale('en');

    $response = $this->getJson('/api/products?color=black&with_facets=1');

    $response->assertOk()->assertJsonPath('total', 1);

    $payload = $response->json();
    $attributes = collect($payload['data'][0]['attributes'])->keyBy('key');

    expect($payload['data'][0]['id'])->toBe($product->id);
    expect($payload['data'][0]['attribute_values']['color'])->toBe('black');
    expect($attributes['color']['label'])->toBe($black['translations']['en']);
    expect($attributes['color']['translations'])->toMatchArray($black['translations']);

    $colorFacet = $payload['facets']['attrs.color']['black'] ?? null;
    expect($colorFacet)->not->toBeNull();
    expect($colorFacet['count'])->toBe(1);
    expect($colorFacet['label'])->toBe($black['translations']['en']);
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
