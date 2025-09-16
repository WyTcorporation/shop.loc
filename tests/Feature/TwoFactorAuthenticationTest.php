<?php

use App\Models\TwoFactorSecret;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('generates a two-factor secret for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/profile/two-factor')
        ->assertCreated()
        ->json();

    expect($response)
        ->toHaveKey('secret')
        ->and($response['secret'])->toBeString()->not->toBeEmpty()
        ->and($response)
        ->toHaveKey('otpauth_url');

    $this->assertDatabaseHas('two_factor_secrets', [
        'user_id' => $user->id,
        'confirmed_at' => null,
    ]);
});

it('confirms two-factor setup and requires otp during login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    $this->actingAs($user, 'sanctum');

    $setup = $this->postJson('/api/profile/two-factor')
        ->assertCreated()
        ->json();

    $service = app(TwoFactorService::class);
    $code = $service->getCurrentCode($setup['secret']);
    expect($code)->not->toBeNull();

    $this->postJson('/api/profile/two-factor/confirm', ['code' => $code])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Двофакторну автентифікацію увімкнено.']);

    tap(TwoFactorSecret::where('user_id', $user->id)->first(), function ($secret) {
        expect($secret)->not->toBeNull();
        expect($secret->confirmed_at)->not->toBeNull();
    });

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertStatus(409)->assertJson(['two_factor_required' => true]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
        'otp' => '000000',
    ])->assertStatus(422)->assertJsonStructure(['errors' => ['otp']]);

    $validCode = $service->getCurrentCode($setup['secret']);

    $login = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
        'otp' => $validCode,
    ])->assertOk()->json();

    expect($login)
        ->toHaveKey('token')
        ->and($login['token'])->toBeString()->not->toBeEmpty()
        ->and($login)
        ->toHaveKey('user')
        ->and($login['user']['two_factor_enabled'] ?? false)->toBeTrue();

    expect(DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->count())->toBeGreaterThan(0);
});

it('can disable two-factor authentication', function () {
    $secret = TwoFactorSecret::factory()->create();
    $user = $secret->user;

    $this->actingAs($user, 'sanctum');

    $this->deleteJson('/api/profile/two-factor')
        ->assertNoContent();

    $this->assertDatabaseMissing('two_factor_secrets', [
        'id' => $secret->id,
    ]);
});
