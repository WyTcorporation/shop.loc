<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function build(): self
    {
        return $this->subject('Ласкаво просимо до Shop')
            ->tag('auth-welcome')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.welcome', [
                'user' => $this->user,
            ]);
    }
}
