<?php

use App\Enums\OrderStatus;
use App\Enums\Permission as PermissionEnum;
use App\Filament\Mine\Resources\Orders\Pages\ListOrders;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

afterEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('creates a paid invoice when marking an order paid via filament', function () {
    $user = User::factory()->create();
    Permission::findOrCreate(PermissionEnum::ManageOrders->value, 'web');
    $user->givePermissionTo(PermissionEnum::ManageOrders->value);

    $this->actingAs($user);

    $product = Product::factory()->create([
        'stock' => 10,
        'price' => 50,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::New,
        'currency' => 'USD',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 50,
    ]);

    $order->recalculateTotal();
    $order->refresh();

    expect(Invoice::count())->toBe(0);

    Livewire::test(ListOrders::class)
        ->callTableAction('markPaid', $order->getKey());

    $order->refresh();
    $invoice = Invoice::where('order_id', $order->id)->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->order_id)->toBe($order->id);
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->subtotal)->toBe((float) $order->subtotal);
    expect((float) $invoice->tax_total)->toBe(0.0);
    expect((float) $invoice->total)->toBe((float) $order->total);
    expect($invoice->number)->toMatch('/^INV-/');
});

it('creates a paid invoice when a stripe payment intent succeeds', function () {
    $product = Product::factory()->create([
        'stock' => 5,
        'price' => 25,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::New,
        'currency' => 'USD',
        'payment_intent_id' => 'pi_test_123',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => 25,
    ]);

    $order->recalculateTotal();
    $order->refresh();

    $data = [
        'id' => 'evt_test_123',
        'object' => 'event',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'object' => 'payment_intent',
                'status' => 'succeeded',
                'metadata' => [
                    'order_number' => null,
                ],
            ],
        ],
    ];

    $payload = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

    $secret = 'whsec_test';
    $timestamp = (string) now()->timestamp;
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    $signatureHeader = 't=' . $timestamp . ',v1=' . $signature;

    config(['services.stripe.webhook_secret' => $secret]);

    $request = Request::create(
        '/stripe/webhook',
        'POST',
        content: $payload,
    );

    $request->headers->set('Stripe-Signature', $signatureHeader);
    $request->headers->set('Content-Type', 'application/json');

    $response = app(StripeWebhookController::class)->handle($request);

    expect($response->getStatusCode())->toBe(200);

    $order->refresh();
    $invoice = Invoice::where('order_id', $order->id)->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->subtotal)->toBe((float) $order->subtotal);
    expect((float) $invoice->tax_total)->toBe(0.0);
    expect((float) $invoice->total)->toBe((float) $order->total);
});
