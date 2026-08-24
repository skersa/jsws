<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to:      [new Address(config('mail.contact_address', 'noreply@artirdim.com'))],
            replyTo: [new Address($this->name, $this->email)],
            subject: '[İletişim] ' . $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }
}
