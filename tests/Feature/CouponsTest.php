<?php

use App\Models\{Cart, CartItem, Coupon, Product};

it('applies a coupon to the cart', function () {
    $product = Product::factory()->create([
        'price' => 100,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price' => $product->price,
    ]);

    $coupon = Coupon::create([
        'code' => 'SAVE10',
        'type' => Coupon::TYPE_PERCENT,
        'value' => 10,
        'min_cart_total' => 0,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/cart/apply-coupon', [
        'cart_id' => $cart->id,
        'code' => $coupon->code,
    ])->assertOk();

    $cart->refresh();

    expect($cart->coupon_id)->toBe($coupon->id);
    expect($cart->coupon_code)->toBe($coupon->code);

    expect($response->json('discounts.coupon.code'))->toBe($coupon->code);
    expect($response->json('discounts.coupon.amount'))->toEqualWithDelta(20.0, 0.01);
    expect($response->json('discounts.total'))->toEqualWithDelta(20.0, 0.01);
    expect($response->json('total'))->toEqualWithDelta(180.0, 0.01);
});

it('rejects invalid coupon codes', function () {
    $product = Product::factory()->create([
        'price' => 50,
        'stock' => 5,
    ]);

    $cart = Cart::factory()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'price' => $product->price,
    ]);

    $this->postJson('/api/cart/apply-coupon', [
        'cart_id' => $cart->id,
        'code' => 'INVALID',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['code']);

    $cart->refresh();

    expect($cart->coupon_id)->toBeNull();
    expect($cart->coupon_code)->toBeNull();
});
