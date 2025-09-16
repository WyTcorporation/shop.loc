<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $shipping = [
            'name' => $this->faker->firstName(),
            'city' => $this->faker->city(),
            'addr' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
        ];

        return [
            'user_id' => null, // за замовчуванням — гість
            'email'   => $this->faker->safeEmail(),
            'status'  => OrderStatus::New,            // enum каст у моделі
            'total'   => 0,
            'shipping_address' => $shipping,
            'shipping_address_id' => Address::factory()->state(function () use ($shipping) {
                return [
                    'user_id' => null,
                    'name' => $shipping['name'],
                    'city' => $shipping['city'],
                    'addr' => $shipping['addr'],
                    'postal_code' => $shipping['postal_code'],
                    'phone' => $shipping['phone'],
                ];
            }),
            'billing_address' => null,
            'note'    => null,
            'number'  => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(16)),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Paid]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            if (! $order->shipment()->exists()) {
                $order->shipment()->create([
                    'address_id' => $order->shipping_address_id,
                    'status' => ShipmentStatus::Pending,
                ]);
            }
        });
    }
}
