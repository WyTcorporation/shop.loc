<?php

use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Stripe\StripeClient;

afterEach(function (): void {
    app()->forgetInstance(StripeClient::class);
});

it('creates an invoice when refreshing status succeeds', function () {
    config(['services.stripe.secret' => 'sk_test_refresh_status']);

    $product = Product::factory()->create([
        'stock' => 5,
        'price' => 25,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::New,
        'currency' => 'USD',
        'payment_intent_id' => 'pi_test_refresh',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 25,
    ]);

    $order->recalculateTotal();
    $order->refresh();

    expect(Invoice::count())->toBe(0);

    $paymentIntentId = 'pi_test_refresh';
    $paymentIntent = (object) [
        'id' => $paymentIntentId,
        'status' => 'succeeded',
    ];

    $paymentIntentService = new class($paymentIntentId, $paymentIntent) {
        public function __construct(private string $expectedId, private object $paymentIntent)
        {
        }

        public function retrieve(string $id): object
        {
            if ($id !== $this->expectedId) {
                throw new \InvalidArgumentException('Unexpected payment intent id.');
            }

            return $this->paymentIntent;
        }
    };

    $stripeClient = new class($paymentIntentService) {
        public function __construct(private object $paymentIntentService)
        {
        }

        public function __get(string $name): object
        {
            if ($name !== 'paymentIntents') {
                throw new \InvalidArgumentException('Unexpected service access: ' . $name);
            }

            return $this->paymentIntentService;
        }
    };

    app()->instance(StripeClient::class, $stripeClient);

    expect(app()->bound(StripeClient::class))->toBeTrue();
    expect(app(StripeClient::class))->toBe($stripeClient);

    $response = $this->postJson("/api/payment/refresh/{$order->number}", [
        'payment_intent' => $paymentIntentId,
    ]);

    $response->assertOk();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Paid);
    expect(Invoice::where('order_id', $order->id)->exists())->toBeTrue();
    expect(Invoice::where('order_id', $order->id)->count())->toBe(1);
});
