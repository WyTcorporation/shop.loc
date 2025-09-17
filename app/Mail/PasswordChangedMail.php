<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function build(): self
    {
        return $this->subject('Ваш пароль до Shop змінено')
            ->tag('auth-password-changed')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.password-changed', [
                'user' => $this->user,
            ]);
    }
}
