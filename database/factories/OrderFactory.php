<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => null, // за замовчуванням — гість
            'email'   => $this->faker->safeEmail(),
            'status'  => OrderStatus::New,            // enum каст у моделі
            'total'   => 0,
            'shipping_address' => [
                'name' => $this->faker->firstName(),
                'city' => $this->faker->city(),
                'addr' => $this->faker->streetAddress(),
            ],
            'billing_address' => null,
            'note'    => null,
            'number'  => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(16)),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Paid]);
    }
}
