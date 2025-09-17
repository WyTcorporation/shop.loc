<?php

use App\Models\{LoyaltyPointTransaction, User};

it('returns loyalty point summary for authenticated user', function () {
    $user = User::factory()->create();

    LoyaltyPointTransaction::factory()
        ->for($user)
        ->earn(120)
        ->create([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

    LoyaltyPointTransaction::factory()
        ->for($user)
        ->redeem(40)
        ->create([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

    LoyaltyPointTransaction::factory()
        ->for($user)
        ->adjustment(-10)
        ->create([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    $latest = LoyaltyPointTransaction::factory()
        ->for($user)
        ->adjustment(15)
        ->create([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/profile/points')->assertOk();

    $response->assertJson([
        'balance' => 120 - 40 - 10 + 15,
        'total_earned' => 120 + 15,
        'total_spent' => 40 + 10,
    ]);

    $response->assertJsonCount(4, 'transactions');

    expect($response->json('transactions.0.id'))->toBe($latest->id);
});

it('requires authentication to view loyalty point summary', function () {
    $this->getJson('/api/profile/points')->assertUnauthorized();
});
