<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id'   => Order::factory(),
            'product_id' => Product::factory(),
            'warehouse_id' => fn () => Warehouse::getDefault()->id,
            'qty'        => $this->faker->numberBetween(1, 3),
            'price'      => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}

