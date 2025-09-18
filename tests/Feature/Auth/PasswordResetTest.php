<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('sends a password reset link to an existing user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->postJson('/api/password/email', [
        'email' => $user->email,
    ]);

    $response->assertOk()->assertJson([
        'message' => trans(Password::RESET_LINK_SENT),
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns validation error when email is not found', function () {
    Notification::fake();

    $response = $this->postJson('/api/password/email', [
        'email' => 'missing@example.com',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);

    Notification::assertNothingSent();
});

it('resets the password with a valid token', function () {
    Notification::fake();

    $user = User::factory()->create([
        'password' => Hash::make('initial-password'),
    ]);

    $token = Password::broker()->createToken($user);

    $response = $this->postJson('/api/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertOk()->assertJson([
        'message' => trans(Password::PASSWORD_RESET),
    ]);

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});

it('logs the user in and redirects to profile after resetting the password via web form', function () {
    $user = User::factory()->create([
        'password' => Hash::make('initial-password'),
    ]);

    $token = Password::broker()->createToken($user);

    Session::start();

    $response = $this->post('/reset-password', [
        '_token' => Session::token(),
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect('/profile');
    $response->assertSessionHas('status', trans(Password::PASSWORD_RESET));

    $this->assertAuthenticatedAs($user);
    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});
