<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Database\Support\TranslationGenerator;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $defaultLocale = config('app.locale');
        $labels = TranslationGenerator::warehouseTexts('regional');
        $name = $labels['name'][$defaultLocale] ?? reset($labels['name']);
        $description = $labels['description'][$defaultLocale] ?? reset($labels['description']);

        return [
            'code' => Str::upper('WH-' . $this->faker->unique()->lexify('????')),
            'name' => $name,
            'name_translations' => $labels['name'],
            'description' => $description,
            'description_translations' => $labels['description'],
        ];
    }
}
