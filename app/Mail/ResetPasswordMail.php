<?php

namespace App\Mail;

use App\Models\User;
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
        $appName = config('app.name', 'Shop');

        return $this->subject(__('Скидання пароля для :app', ['app' => $appName]))
            ->tag('auth-password-reset')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.reset-password', [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'displayUrl' => $this->displayUrl,
            ]);
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
