<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $warehouse = Warehouse::getDefault();

            $product->stocks()->updateOrCreate(
                ['warehouse_id' => $warehouse->id],
                [
                    'qty' => (int) $product->stock,
                    'reserved' => 0,
                ]
            );

            $product->syncAvailableStock();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(3, true));
        $price = $this->faker->randomFloat(2, 10, 500);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'sku' => Str::upper(Str::random(10)),
            'category_id' => Category::factory(),
            'attributes' => [
                'size' => $this->faker->randomElement(['S','M','L']),
                'color' => $this->faker->safeColorName(),
            ],
            'stock' => $this->faker->numberBetween(0, 100),
            'price' => $price,
            'price_cents' => (int) round($price * 100),
            'price_old' => null,
            'is_active' => true,
        ];
    }
}
