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

    public string $displayUrl;

    public function __construct(public User $user, public string $verificationUrl)
    {
        $this->displayUrl = $this->makeDisplayUrl($verificationUrl);
    }

    public function build(): self
    {
        $appName = config('app.name', 'Shop');

        return $this->subject(__('shop.auth.verify.subject', ['app' => $appName]))
            ->tag('auth-verify-email')
            ->metadata(['type' => 'auth'])
            ->view('emails.auth.verify-email', [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'displayUrl' => $this->displayUrl,
            ]);
    }

    protected function makeDisplayUrl(string $url): string
    {
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
