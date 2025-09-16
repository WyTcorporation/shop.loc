<?php

namespace Database\Factories;

use App\Models\TwoFactorSecret;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TwoFactorSecret>
 */
class TwoFactorSecretFactory extends Factory
{
    protected $model = TwoFactorSecret::class;

    public function definition(): array
    {
        $alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

        return [
            'user_id' => User::factory(),
            'secret' => collect(range(1, 32))
                ->map(fn () => $alphabet[array_rand($alphabet)])
                ->implode(''),
            'confirmed_at' => now(),
        ];
    }

    public function unconfirmed(): static
    {
        return $this->state(fn () => [
            'confirmed_at' => null,
        ]);
    }
}
