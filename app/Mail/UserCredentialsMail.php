<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
        public string $context,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciais de acesso — GILbank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-credentials',
        );
    }
}
