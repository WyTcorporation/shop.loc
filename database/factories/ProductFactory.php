<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(mt_rand(2, 4), true);

        return [
            'name'        => Str::title($name),
            'slug'        => Str::slug($name.'-'.Str::random(6)),
            'sku'         => strtoupper(Str::random(10)),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'attributes'  => [
                'color' => $this->faker->safeColorName(),
                'size'  => $this->faker->randomElement(['S', 'M', 'L']),
            ],
            'stock'     => $this->faker->numberBetween(0, 100),
            'price'     => $this->faker->randomFloat(2, 10, 500),
            'price_old' => $this->faker->optional(0.3)->randomFloat(2, 10, 500),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
