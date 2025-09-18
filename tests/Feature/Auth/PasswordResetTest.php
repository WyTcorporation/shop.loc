<?php

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('sends a password reset link to an existing user', function () {
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->postJson('/api/password/email', [
        'email' => $user->email,
    ]);

    $response->assertOk()->assertJson([
        'message' => trans(Password::RESET_LINK_SENT),
    ]);

    Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
        expect($mail->hasTo($user->email))->toBeTrue();

        return true;
    });
});

it('returns validation error when email is not found', function () {
    Mail::fake();

    $response = $this->postJson('/api/password/email', [
        'email' => 'missing@example.com',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);

    Mail::assertNothingQueued();
});

it('queues branded password reset mail when sending reset link directly', function () {
    Mail::fake();

    $user = User::factory()->create();

    $status = Password::sendResetLink([
        'email' => $user->email,
    ]);

    expect($status)->toBe(Password::RESET_LINK_SENT);

    Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
        expect($mail->hasTo($user->email))->toBeTrue();

        $html = $mail->render();

        expect($html)->toContain('Скинути пароль');
        expect($html)->toContain(config('app.name', 'Shop'));

        return true;
    });
});

it('resets the password with a valid token', function () {
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

it('returns localized error message when reset token is expired', function () {
    app()->setLocale('uk');

    $user = User::factory()->create([
        'password' => Hash::make('initial-password'),
    ]);

    $token = Password::broker()->createToken($user);

    DB::table(config('auth.passwords.users.table'))->where('email', $user->email)->update([
        'created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1),
    ]);

    $response = $this->postJson('/api/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertStatus(422)->assertJson([
        'message' => trans('passwords.token'),
    ]);
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
