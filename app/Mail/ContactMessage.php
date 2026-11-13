<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessage extends Mailable
{
    public function __construct(
        public readonly ?string $name,
        public readonly string $email,
        public readonly ?string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->email, $this->name ?: null)],
            subject: 'Mam proces, który wymaga automatyzacji.',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
