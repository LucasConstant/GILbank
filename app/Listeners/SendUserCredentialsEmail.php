<?php

namespace App\Listeners;

use App\Events\UserCredentialsCreated;
use App\Mail\UserCredentialsMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserCredentialsEmail implements ShouldQueue
{
    public function handle(UserCredentialsCreated $event): void
    {
        Mail::to($event->user->email)->send(
            new UserCredentialsMail(
                user: $event->user,
                plainPassword: $event->plainPassword,
                context: $event->context,
            )
        );
    }
}
