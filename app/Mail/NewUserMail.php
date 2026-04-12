<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $userName,
        public string $userEmail,
        public string $generatedPassword,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your account for ' . $this->tenantName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-user',
            with: [
                'tenantName' => $this->tenantName,
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'generatedPassword' => $this->generatedPassword,
            ],
        );
    }
}
