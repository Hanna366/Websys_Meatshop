<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $userName,
        public string $userEmail,
        public string $username,
        public string $role,
        public string $generatedPassword,
        public string $loginUrl,
        public string $resetUrl
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = "Your {$this->tenantName} account is ready — access details";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-credentials',
            with: [
                'tenantName' => $this->tenantName,
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'username' => $this->username,
                'role' => $this->role,
                'generatedPassword' => $this->generatedPassword,
                'loginUrl' => $this->loginUrl,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
