<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Database\Support\TranslationGenerator;

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
        $themes = TranslationGenerator::themes();
        $theme = $themes[array_rand($themes)];
        $translations = TranslationGenerator::categoryName($theme);
        $defaultLocale = config('app.locale');
        $name = $translations[$defaultLocale] ?? reset($translations);

        return [
            'name' => $name,
            'name_translations' => $translations,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
