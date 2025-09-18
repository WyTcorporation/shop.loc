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

    public ?string $displayUrl;

    public function __construct(public User $user, public ?string $verificationUrl = null)
    {
        $this->displayUrl = $this->makeDisplayUrl($verificationUrl);
    }

    public function build(): self
    {
        $appName = config('app.name', 'Shop');

        return $this->subject(__('shop.auth.welcome.subject', ['app' => $appName]))
            ->tag('auth-welcome')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.welcome', [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'displayUrl' => $this->displayUrl,
            ]);
    }

    protected function makeDisplayUrl(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }

        $frontendUrl = config('app.frontend_url');

        if (! $frontendUrl) {
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
