<?php

use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    config(['auth.guards.sanctum' => [
        'driver' => 'session',
        'provider' => 'users',
    ]]);

    config(['app.frontend_url' => 'https://shop-frontend.example']);

    App::setLocale('uk');
});

it('sends verification and welcome mails when registering', function () {
    Mail::fake();

    $response = $this->withUnencryptedCookie('lang', 'pt')->withCredentials()->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'super-secret',
    ])->assertCreated();

    /** @var User|null $user */
    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        expect($mail->verificationUrl)->toBeString();
        expect($mail->displayUrl)->toBe(expectedDisplayUrl($mail->verificationUrl));
        expect($mail->locale)->toBe('pt');

        $rendered = $mail->render();
        $escapedUrl = e($mail->verificationUrl);
        $escapedDisplayUrl = e($mail->displayUrl);

        expect($rendered)->toContain('href="' . $escapedUrl . '"');
        expect($rendered)->toContain('>' . $escapedDisplayUrl . '<');

        return $mail->hasTo($user->email);
    });

    Mail::assertQueued(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
        expect($mail->verificationUrl)->toBeString();
        expect($mail->displayUrl)->toBe(expectedDisplayUrl($mail->verificationUrl));
        expect($mail->locale)->toBe('pt');

        $rendered = $mail->render();
        $escapedUrl = e($mail->verificationUrl);
        $escapedDisplayUrl = e($mail->displayUrl);

        expect($rendered)->toContain('href="' . $escapedUrl . '"');
        expect($rendered)->toContain('>' . $escapedDisplayUrl . '<');

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

    App::setLocale('uk');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson(['message' => __('shop.api.auth.verification_link_sent')]);

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user) {
        expect($mail->displayUrl)->toBe(expectedDisplayUrl($mail->verificationUrl));

        $rendered = $mail->render();
        $escapedUrl = e($mail->verificationUrl);
        $escapedDisplayUrl = e($mail->displayUrl);

        expect($rendered)->toContain('href="' . $escapedUrl . '"');
        expect($rendered)->toContain('>' . $escapedDisplayUrl . '<');

        return $mail->hasTo($user->email);
    });
});

it('verifies the email address via the API endpoint', function () {
    Mail::fake();

    App::setLocale('uk');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')->assertStatus(202);

    $verificationUrl = null;

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user, &$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;
        $displayUrl = $mail->displayUrl;

        expect($displayUrl)->toBe(expectedDisplayUrl($mail->verificationUrl));

        $rendered = $mail->render();
        $escapedUrl = e($mail->verificationUrl);
        $escapedDisplayUrl = e($displayUrl);

        expect($rendered)->toContain('href="' . $escapedUrl . '"');
        expect($rendered)->toContain('>' . $escapedDisplayUrl . '<');

        return $mail->hasTo($user->email);
    });

    expect($verificationUrl)->not->toBeNull();

    $this->getJson($verificationUrl)
        ->assertOk()
        ->assertJson(['message' => __('shop.api.verify_email.verified')]);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('resends the verification email with Russian localization', function () {
    Mail::fake();

    App::setLocale('ru');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson(['message' => __('shop.api.auth.verification_link_sent')]);
});

it('resends the verification email with Portuguese localization', function () {
    Mail::fake();

    App::setLocale('pt');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')
        ->assertStatus(202)
        ->assertJson(['message' => __('shop.api.auth.verification_link_sent')]);
});

it('verifies the email address via the API endpoint with Russian localization', function () {
    Mail::fake();

    App::setLocale('ru');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')->assertStatus(202);

    $verificationUrl = null;

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user, &$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;

        return $mail->hasTo($user->email);
    });

    expect($verificationUrl)->not->toBeNull();

    $this->getJson($verificationUrl)
        ->assertOk()
        ->assertJson(['message' => __('shop.api.verify_email.verified')]);
});

it('verifies the email address via the API endpoint with Portuguese localization', function () {
    Mail::fake();

    App::setLocale('pt');

    $user = User::factory()->unverified()->create();

    $this->actingAs($user, 'sanctum');

    $this->postJson('/api/email/resend')->assertStatus(202);

    $verificationUrl = null;

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) use ($user, &$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;

        return $mail->hasTo($user->email);
    });

    expect($verificationUrl)->not->toBeNull();

    $this->getJson($verificationUrl)
        ->assertOk()
        ->assertJson(['message' => __('shop.api.verify_email.verified')]);
});

function expectedDisplayUrl(?string $url): ?string
{
    if (! $url) {
        return $url;
    }

    $frontendUrl = config('app.frontend_url');

    if (! $frontendUrl) {
        return $url;
    }

    $frontendUrl = rtrim($frontendUrl, '/');
    $parts = parse_url($url);

    if ($parts === false) {
        return $url;
    }

    $path = $parts['path'] ?? '';
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

    return $frontendUrl . $path . $query . $fragment;
}
