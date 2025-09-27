<?php

use App\Mail\PasswordChangedMail;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

it('queues password changed email when password is reset via the api', function () {
    Mail::fake();

    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = $this->withUnencryptedCookie('lang', 'ru')->withCredentials()->postJson('/api/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertOk();

    Mail::assertQueued(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
        $mail->assertHasTag('buyer')
            ->assertHasMetadata('type', 'auth')
            ->assertHasMetadata('mail_type', 'auth-password-changed');
        expect($mail->locale)->toBe('ru');

        return $mail->user->is($user);
    });
});

it('queues reset password email with locale from cookie', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->withUnencryptedCookie('lang', 'pt')->withCredentials()->postJson('/api/password/email', [
        'email' => $user->email,
    ])->assertOk();

    Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
        $mail->assertHasTag('buyer')
            ->assertHasMetadata('type', 'auth')
            ->assertHasMetadata('mail_type', 'auth-password-reset');
        expect($mail->locale)->toBe('pt');

        return $mail->user->is($user);
    });
});
