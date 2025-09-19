<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Database\Support\TranslationGenerator;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $set = TranslationGenerator::vendorSet();
        $nameTranslations = $set['name'];
        $descriptionTranslations = $set['description'];
        $defaultLocale = config('app.locale');
        $name = $nameTranslations[$defaultLocale] ?? reset($nameTranslations);
        $description = $descriptionTranslations[$defaultLocale] ?? reset($descriptionTranslations);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'name_translations' => $nameTranslations,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'contact_email' => $this->faker->unique()->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'description' => $description,
            'description_translations' => $descriptionTranslations,
        ];
    }
}
