<?php

use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

it('queues password changed email when password is reset via the api', function () {
    Mail::fake();

    $user = User::factory()->create();

    $token = Password::broker()->createToken($user);

    $response = $this->postJson('/api/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertOk();

    Mail::assertQueued(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
        $mail->assertHasTag('auth-password-changed')->assertHasMetadata('type', 'auth');

        return $mail->user->is($user);
    });
});
