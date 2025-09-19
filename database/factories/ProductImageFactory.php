<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = config('app.locale');
        $alt = fake()->words(3, true);

        return [
            'path'       => 'products/tmp/'.fake()->uuid().'.png', // справжній шлях виставимо в сідері
            'alt'        => $alt,
            'alt_translations' => [$locale => $alt],
            'disk'       => 'public',
            'sort'       => 0,
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'sort' => 0]);
    }
}
