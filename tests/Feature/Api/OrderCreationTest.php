<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_order_item_to_warehouse_with_available_stock(): void
    {
        $defaultWarehouse = Warehouse::factory()->create();
        $secondaryWarehouse = Warehouse::factory()->create();

        $product = Product::factory()->create(['stock' => 0]);

        $product->stocks()->updateOrCreate(
            ['warehouse_id' => $secondaryWarehouse->id],
            ['qty' => 5, 'reserved' => 0],
        );
        $product->syncAvailableStock();

        $cart = Cart::factory()->create();

        CartItem::factory()
            ->for($cart)
            ->for($product)
            ->create([
                'qty' => 2,
                'price' => 99.99,
            ]);

        $payload = [
            'cart_id' => $cart->id,
            'email' => 'customer@example.com',
            'shipping_address' => [
                'name' => 'Test Customer',
                'city' => 'Kyiv',
                'addr' => 'Shevchenka St, 1',
                'postal_code' => '01001',
                'phone' => '+3800000000',
            ],
            'delivery_method' => 'nova',
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'warehouse_id' => $secondaryWarehouse->id,
            'qty' => 2,
        ]);

        $this->assertSame('ordered', $cart->fresh()->status);

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertSame($secondaryWarehouse->id, $order->items()->first()->warehouse_id);
    }

    public function test_it_returns_sold_out_when_no_warehouse_has_stock(): void
    {
        Warehouse::factory()->create();

        $product = Product::factory()->create(['stock' => 0]);
        $product->syncAvailableStock();

        $cart = Cart::factory()->create();

        CartItem::factory()
            ->for($cart)
            ->for($product)
            ->create([
                'qty' => 1,
                'price' => 120,
            ]);

        $payload = [
            'cart_id' => $cart->id,
            'email' => 'customer@example.com',
            'shipping_address' => [
                'name' => 'Test Customer',
                'city' => 'Kyiv',
                'addr' => 'Shevchenka St, 1',
                'postal_code' => '01001',
                'phone' => '+3800000000',
            ],
            'delivery_method' => 'nova',
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(409)->assertJson([
            'code' => 'sold_out',
            'product_id' => $product->id,
        ]);

        $this->assertSame(0, Order::count());
        $this->assertSame('active', $cart->fresh()->status);
    }
}
