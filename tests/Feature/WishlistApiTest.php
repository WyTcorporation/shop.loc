<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('returns wishlist items for an authenticated user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->createQuietly();

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    Sanctum::actingAs($user, [], 'sanctum');

    getJson('/api/profile/wishlist')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $product->id,
            'name' => $product->name,
        ]);
});

it('upserts wishlist items when syncing after login', function () {
    $user = User::factory()->create();
    $product = Product::factory()->createQuietly();

    Sanctum::actingAs($user, [], 'sanctum');

    Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));

    postJson("/api/profile/wishlist/{$product->id}")
        ->assertOk()
        ->assertJsonFragment([
            'id' => $product->id,
        ]);

    expect(Wishlist::query()->count())->toBe(1);

    $originalUpdatedAt = Wishlist::query()->first()->updated_at;

    Carbon::setTestNow(Carbon::parse('2024-01-01 12:05:00'));

    postJson("/api/profile/wishlist/{$product->id}")
        ->assertOk();

    Carbon::setTestNow();

    expect(Wishlist::query()->count())->toBe(1)
        ->and(Wishlist::query()->first()->updated_at->gt($originalUpdatedAt))->toBeTrue();

    getJson('/api/profile/wishlist')
        ->assertOk()
        ->assertJsonCount(1);
});
