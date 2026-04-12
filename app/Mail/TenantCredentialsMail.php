<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $adminName,
        public string $adminEmail,
        public ?string $generatedPassword = null,
        public ?string $plan = null,
        public string $loginUrl = '',
        public string $resetUrl = ''
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[MeatShopPOS] Your tenant is ready — access details");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-credentials',
            with: [
                'tenantName' => $this->tenantName,
                'adminName' => $this->adminName,
                'adminEmail' => $this->adminEmail,
                'generatedPassword' => $this->generatedPassword,
                'plan' => $this->plan,
                'loginUrl' => $this->loginUrl,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
