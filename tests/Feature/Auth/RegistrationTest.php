<?php

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('sends verification notification and welcome mail when registering', function () {
    Notification::fake();
    Mail::fake();

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'super-secret',
    ])->assertCreated();

    /** @var User|null $user */
    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);

    Mail::assertQueued(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    $response->assertJsonStructure([
        'token',
        'user' => [
            'id',
            'name',
            'email',
            'email_verified_at',
            'two_factor_enabled',
            'two_factor_confirmed_at',
        ],
    ]);
});

it('resends the verification email for authenticated users', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson(['message' => 'Verification link sent.']);

    Notification::assertSentTo($user->fresh(), VerifyEmail::class);
});
