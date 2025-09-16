<?php

use App\Models\{Cart, CartItem, LoyaltyPointTransaction, Order, Product, User};

it('accrues loyalty points when order is placed', function () {
    config([
        'shop.loyalty.earn_rate' => 1.0,
        'shop.loyalty.redeem_value' => 0.1,
        'shop.loyalty.max_redeem_percent' => 1.0,
    ]);

    $user = User::factory()->create();

    $product = Product::factory()->create([
        'price' => 30,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create([
        'user_id' => $user->id,
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 3,
        'price' => $product->price,
    ]);

    $payload = [
        'cart_id' => $cart->id,
        'email' => $user->email,
        'shipping_address' => [
            'name' => 'Test User',
            'city' => 'Kyiv',
            'addr' => 'Main street',
        ],
    ];

    $this->postJson('/api/orders', $payload)->assertCreated();

    $order = Order::with('user')->first();

    expect($order)->not->toBeNull();
    expect($order->loyalty_points_used)->toBe(0);
    expect($order->loyalty_points_value)->toEqualWithDelta(0.0, 0.01);
    expect($order->loyalty_points_earned)->toBe(90);

    $transaction = LoyaltyPointTransaction::where('order_id', $order->id)
        ->where('type', LoyaltyPointTransaction::TYPE_EARN)
        ->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->points)->toBe($order->loyalty_points_earned);
    expect($transaction->amount)->toEqualWithDelta($order->total, 0.01);

    expect($order->user->fresh()->loyalty_points_balance)->toBe($order->loyalty_points_earned);
});

it('redeems loyalty points during checkout and deducts them', function () {
    config([
        'shop.loyalty.earn_rate' => 1.0,
        'shop.loyalty.redeem_value' => 0.1,
        'shop.loyalty.max_redeem_percent' => 1.0,
    ]);

    $user = User::factory()->create();

    LoyaltyPointTransaction::create([
        'user_id' => $user->id,
        'type' => LoyaltyPointTransaction::TYPE_EARN,
        'points' => 200,
        'amount' => 20,
        'description' => 'Initial balance',
    ]);

    $product = Product::factory()->create([
        'price' => 50,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create([
        'user_id' => $user->id,
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => $product->price,
    ]);

    $applyResponse = $this->postJson('/api/cart/apply-points', [
        'cart_id' => $cart->id,
        'points' => 150,
    ])->assertOk();

    expect($applyResponse->json('discounts.loyalty_points.used'))->toBe(150);
    expect($applyResponse->json('discounts.loyalty_points.value'))->toEqualWithDelta(15.0, 0.01);

    $cart->refresh();
    expect($cart->loyalty_points_used)->toBe(150);

    $payload = [
        'cart_id' => $cart->id,
        'email' => $user->email,
        'shipping_address' => [
            'name' => 'Test User',
            'city' => 'Kyiv',
            'addr' => 'Main street',
        ],
    ];

    $this->postJson('/api/orders', $payload)->assertCreated();

    $order = Order::with('user')->first();

    expect($order)->not->toBeNull();
    expect($order->loyalty_points_used)->toBe(150);
    expect($order->loyalty_points_value)->toEqualWithDelta(15.0, 0.01);
    expect($order->total)->toEqualWithDelta(85.0, 0.01);
    expect($order->loyalty_points_earned)->toBe(85);

    $transactions = LoyaltyPointTransaction::where('order_id', $order->id)->get();
    expect($transactions)->toHaveCount(2);

    $redeem = $transactions->firstWhere('type', LoyaltyPointTransaction::TYPE_REDEEM);
    expect($redeem)->not->toBeNull();
    expect($redeem->points)->toBe(-150);
    expect($redeem->amount)->toEqualWithDelta(15.0, 0.01);

    $earn = $transactions->firstWhere('type', LoyaltyPointTransaction::TYPE_EARN);
    expect($earn)->not->toBeNull();
    expect($earn->points)->toBe(85);

    expect($order->user->fresh()->loyalty_points_balance)->toBe(200 - 150 + 85);
});
