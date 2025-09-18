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
        $appName = config('app.name', 'Shop');

        return $this->subject(__('shop.auth.reset.changed_subject', ['app' => $appName]))
            ->tag('auth-password-changed')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.password-changed', [
                'user' => $this->user,
            ]);
    }
}
