<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $locale = config('app.locale');
        $name = $this->faker->unique()->company . ' Warehouse';
        $description = $this->faker->optional()->sentence();

        return [
            'code' => Str::upper('WH-' . $this->faker->unique()->lexify('????')),
            'name' => $name,
            'name_translations' => [$locale => $name],
            'description' => $description,
            'description_translations' => $description !== null ? [$locale => $description] : [],
        ];
    }
}
