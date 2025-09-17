<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('redirects to configured frontend profile path after verification', function () {
    config([
        'app.frontend_url' => 'https://frontend.test',
        'app.frontend_verified_path' => '/profile?email_verified=1',
        'app.frontend_verified_already_path' => '/profile?email_verified=already',
    ]);

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'api.email.verify',
        now()->addHour(),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $this->get($verificationUrl)
        ->assertRedirect('https://frontend.test/profile?email_verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('uses the configured already verified path when applicable', function () {
    config([
        'app.frontend_url' => 'https://frontend.test',
        'app.frontend_verified_path' => '/profile?email_verified=1',
        'app.frontend_verified_already_path' => '/profile?email_verified=already',
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'api.email.verify',
        now()->addHour(),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $this->get($verificationUrl)
        ->assertRedirect('https://frontend.test/profile?email_verified=already');
});
