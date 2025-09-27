<?php

namespace App\Mail;

use App\Models\User;
use App\Support\Mail\UserRoleTag;
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
        $locale = $this->locale ?: app()->getLocale() ?: (string) config('app.fallback_locale', 'en');

        return $this->withLocale($locale, function () use ($locale) {
            $appName = config('app.name', 'Shop');

            $user = $this->user->loadMissing('roles');
            $tag = UserRoleTag::primaryRoleSlug($user);

            return $this->subject(__('shop.auth.reset.changed_subject', ['app' => $appName], $locale))
                ->tag($tag)
                ->metadata([
                    'type' => 'auth',
                    'mail_type' => 'auth-password-changed',
                ])
                ->view('emails.auth.password-changed', [
                    'user' => $user,
                ]);
        });
    }
}
