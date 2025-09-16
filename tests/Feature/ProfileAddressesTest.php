<?php

use App\Models\Address;
use App\Models\User;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('lists only current user addresses', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Address::factory()->count(2)->create(['user_id' => $user->id]);
    Address::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/profile/addresses')
        ->assertOk()
        ->json();

    expect($response)->toHaveCount(2);
    expect(collect($response)->pluck('user_id')->unique()->all())->toBe([$user->id]);
});

it('creates a new address', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $payload = [
        'name' => 'John Doe',
        'city' => 'Kyiv',
        'addr' => 'Street 1',
        'postal_code' => '01001',
        'phone' => '+3801234567',
    ];

    $response = $this->postJson('/api/profile/addresses', $payload)
        ->assertCreated();

    $response->assertJsonFragment(['name' => 'John Doe']);
    expect(Address::where('user_id', $user->id)->count())->toBe(1);
});

it('updates an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id, 'city' => 'Kyiv']);

    $this->actingAs($user, 'sanctum');

    $this->patchJson("/api/profile/addresses/{$address->id}", [
        'city' => 'Lviv',
    ])->assertOk()
      ->assertJsonFragment(['city' => 'Lviv']);

    expect($address->refresh()->city)->toBe('Lviv');
});

it('deletes an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum');

    $this->deleteJson("/api/profile/addresses/{$address->id}")
        ->assertNoContent();

    expect(Address::whereKey($address->id)->exists())->toBeFalse();
});

it('does not allow managing addresses of other users', function () {
    $user = User::factory()->create();
    $other = Address::factory()->create();

    $this->actingAs($user, 'sanctum');

    $this->getJson("/api/profile/addresses/{$other->id}")->assertNotFound();
    $this->patchJson("/api/profile/addresses/{$other->id}", ['city' => 'Dnipro'])->assertNotFound();
    $this->deleteJson("/api/profile/addresses/{$other->id}")->assertNotFound();
});
