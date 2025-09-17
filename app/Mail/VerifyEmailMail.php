<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $verificationUrl)
    {
    }

    public function build(): self
    {
        return $this->subject('Підтвердіть електронну адресу для Shop')
            ->tag('auth-verify-email')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.verify-email', [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}
