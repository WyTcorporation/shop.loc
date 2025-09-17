<?php

use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);
});

it('sends verification and welcome mails when registering', function () {
    Mail::fake();

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'super-secret',
    ])->assertCreated();

    /** @var User|null $user */
    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        expect($mail->verificationUrl)->toBeString();

        return $mail->hasTo($user->email);
    });

    Mail::assertQueued(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
        expect($mail->verificationUrl)->toBeString();

        $rendered = $mail->render();
        $escapedUrl = e($mail->verificationUrl);

        expect($rendered)->toContain('href="' . $escapedUrl . '"');
        expect($rendered)->toContain($escapedUrl);

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
    Mail::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson(['message' => 'Verification link sent.']);

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

it('verifies the email address via the API endpoint', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')->assertStatus(202);

    $verificationUrl = null;

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user, &$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;

        expect($mail->render())->toContain(e($mail->verificationUrl));

        return $mail->hasTo($user->email);
    });

    expect($verificationUrl)->not->toBeNull();

    $this->getJson($verificationUrl)
        ->assertOk()
        ->assertJson(['message' => 'Електронну адресу підтверджено.']);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
