<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        $locale = config('app.locale');
        $description = $this->faker->optional()->sentence(10);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'name_translations' => [$locale => $name],
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'contact_email' => $this->faker->unique()->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'description' => $description,
            'description_translations' => $description !== null ? [$locale => $description] : [],
        ];
    }
}
