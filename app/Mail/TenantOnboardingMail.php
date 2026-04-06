<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantOnboardingMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $businessName,
        public string $adminName,
        public string $adminEmail,
        public string $loginUrl,
        public ?string $passwordSetupUrl = null,
        public ?string $plan = null,
        public ?string $generatedPassword = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to MeatShopPOS - Your tenant is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-onboarding',
            with: [
                'businessName' => $this->businessName,
                'adminName' => $this->adminName,
                'adminEmail' => $this->adminEmail,
                'loginUrl' => $this->loginUrl,
                'passwordSetupUrl' => $this->passwordSetupUrl,
                'plan' => $this->plan,
                'generatedPassword' => $this->generatedPassword,
            ],
        );
    }
}
