<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\EngineManager;

uses(RefreshDatabase::class);

it('falls back to localized database search with proper ordering', function () {
    app(EngineManager::class)->extend('throwing', fn () => new class extends \Laravel\Scout\Engines\Engine {
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

    Product::withoutSyncingToSearch(function () {
        Product::factory()->create([
            'name' => 'Base One',
            'name_translations' => ['ru' => 'Ananas'],
            'stock' => 10,
            'price' => 12.50,
        ]);

        Product::factory()->create([
            'name' => 'Base Two',
            'name_translations' => ['ru' => 'Anke'],
            'stock' => 8,
            'price' => 15.00,
        ]);

        Product::factory()->create([
            'name' => 'Base Three',
            'name_translations' => ['en' => 'Anchor'],
            'stock' => 6,
            'price' => 20.00,
        ]);

        Product::factory()->create([
            'name' => 'Base Four',
            'name_translations' => ['pt' => 'Anchois'],
            'stock' => 5,
            'price' => 18.00,
        ]);
    });

    $response = $this->getJson('/api/search/suggestions?q=an&limit=10')
        ->assertOk();

    $payload = $response->json('data');

    expect($payload)->toHaveCount(4);
    expect(array_column($payload, 'name'))->toBe([
        'Ananas',
        'Anchois',
        'Anchor',
        'Anke',
    ]);
    expect($payload[0]['slug'])->not->toBe('');
    expect($payload[0]['name'])->toBe('Ananas');
    expect($payload[0]['name'])->not->toBe('Base One');
    expect($payload[2]['name'])->toBe('Anchor');
    expect($payload[2]['name'])->not->toBe('Base Three');
});
