<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'city' => $this->faker->city(),
            'addr' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
