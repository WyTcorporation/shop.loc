<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(mt_rand(1, 2), true);
        $locale = config('app.locale');

        return [
            'name' => $name,
            'name_translations' => [$locale => $name],
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
