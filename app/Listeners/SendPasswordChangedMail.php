<?php

namespace App\Listeners;

use App\Mail\PasswordChangedMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedMail
{
    public function handle(PasswordReset $event): void
    {
        Mail::to($event->user)->queue(new PasswordChangedMail($event->user));
    }
}
