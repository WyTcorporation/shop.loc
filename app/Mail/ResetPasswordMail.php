<?php

namespace App\Mail;

use App\Models\User;
use App\Support\Mail\UserRoleTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public string $displayUrl;

    public function __construct(public User $user, public string $token)
    {
        $this->resetUrl = $this->makeResetUrl();
        $this->displayUrl = $this->makeDisplayUrl($this->resetUrl);
    }

    public function build(): self
    {
        $locale = $this->locale ?: app()->getLocale() ?: (string) config('app.fallback_locale', 'en');

        return $this->withLocale($locale, function () use ($locale) {
            $appName = config('app.name', 'Shop');

            $user = $this->user->loadMissing('roles');
            $tag = UserRoleTag::primaryRoleSlug($user);

            return $this->subject(__('shop.auth.reset.subject', ['app' => $appName], $locale))
                ->tag($tag)
                ->metadata([
                    'type' => 'auth',
                    'mail_type' => 'auth-password-reset',
                ])
                ->view('emails.auth.reset-password', [
                    'user' => $user,
                    'resetUrl' => $this->resetUrl,
                    'displayUrl' => $this->displayUrl,
                ]);
        });
    }

    protected function makeResetUrl(): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->getEmailForPasswordReset(),
        ], false));
    }

    protected function makeDisplayUrl(string $url): string
    {
        $frontendUrl = config('app.frontend_url');

        if (!$frontendUrl) {
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
}
