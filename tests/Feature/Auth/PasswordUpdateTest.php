<?php

use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

it('queues password changed email when password is updated', function () {
    Mail::fake();

    $user = User::factory()->create([
        'password' => Hash::make('original-password'),
    ]);

    Sanctum::actingAs($user, [], 'sanctum');

    $response = $this->withUnencryptedCookie('lang', 'es')->withCredentials()->putJson('/api/auth/me', [
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertOk();

    Mail::assertQueued(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
        $mail->assertHasTag('auth-password-changed')->assertHasMetadata('type', 'auth');
        expect($mail->locale)->toBe('es');

        return $mail->user->is($user);
    });
});
