<?php

namespace App\Listeners;

use App\Mail\PasswordChangedMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedMail
{
    public function handle(PasswordReset $event): void
    {
        $locale = resolveMailLocale();

        Mail::to($event->user)
            ->locale($locale)
            ->queue((new PasswordChangedMail($event->user))->locale($locale));
    }
}
