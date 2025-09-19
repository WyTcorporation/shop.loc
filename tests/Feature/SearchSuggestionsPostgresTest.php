<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Tests\TestCase;
use Tests\Support\FakePgsqlConnectionResolver;
use Tests\Support\FakePgsqlSqliteConnection;

class SearchSuggestionsPostgresTest extends TestCase
{
    private SQLiteConnection $connection;

    private ConnectionResolverInterface $originalResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->connection = new FakePgsqlSqliteConnection($pdo, ':memory:', '', ['driver' => 'pgsql']);

        $this->originalResolver = Model::getConnectionResolver();

        $resolver = new FakePgsqlConnectionResolver($this->connection);

        Model::setConnectionResolver($resolver);

        DB::shouldReceive('connection')->andReturn($this->connection);

        $schema = $this->connection->getSchemaBuilder();
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
            $table->integer('vendor_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->timestamps();
        });

        $schema->create('product_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('url')->nullable();
        });

        $schema->create('vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });

        $this->connection->flushCapturedQueries();
    }

    protected function tearDown(): void
    {
        $schema = $this->connection->getSchemaBuilder();

        $schema->dropIfExists('product_images');
        $schema->dropIfExists('vendors');
        $schema->dropIfExists('products');

        Model::setConnectionResolver($this->originalResolver);

        parent::tearDown();
    }

    public function test_fallback_search_uses_postgres_jsonb(): void
    {
        app(EngineManager::class)->extend('throwing', fn () => new class extends Engine {
            public function update($models): void {}

            public function delete($models): void {}

            public function search(\Laravel\Scout\Builder $builder)
            {
                throw new \RuntimeException('Scout engine failure');
            }

            public function paginate(\Laravel\Scout\Builder $builder, $perPage, $page)
            {
                return $this->search($builder);
            }

            public function mapIds($results)
            {
                return collect();
            }

            public function map(\Laravel\Scout\Builder $builder, $results, $model)
            {
                return collect();
            }

            public function lazyMap(\Laravel\Scout\Builder $builder, $results, $model)
            {
                return $this->map($builder, $results, $model);
            }

            public function createIndex($name, array $options = []): void {}

            public function deleteIndex($name): void {}

            public function getTotalCount($results)
            {
                return 0;
            }

            public function flush($model): void {}
        });

        config()->set('scout.driver', 'throwing');
        config()->set('app.locale', 'ru');
        config()->set('app.fallback_locale', 'en');
        app()->setLocale('ru');

        Product::query()->insert([
            [
                'name' => 'Base One',
                'name_translations' => json_encode(['ru' => 'Ananas']),
                'slug' => 'base-one',
                'sku' => 'SKU-1',
                'stock' => 10,
                'price' => 12.50,
                'price_cents' => 1250,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Base Two',
                'name_translations' => json_encode(['ru' => 'Anke']),
                'slug' => 'base-two',
                'sku' => 'SKU-2',
                'stock' => 8,
                'price' => 15.00,
                'price_cents' => 1500,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->connection->flushCapturedQueries();

        $response = $this->getJson('/api/search/suggestions?q=an&limit=5')->assertOk();

        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertSame('Ananas', $data[0]['name']);

        $captured = $this->connection->capturedQueries();

        $this->assertNotEmpty($captured);
        $this->assertTrue(
            collect($captured)->contains(fn ($sql) => str_contains($sql, 'jsonb_each_text') && str_contains($sql, 'ILIKE'))
        );
    }
}

