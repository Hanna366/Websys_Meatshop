<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSignupConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Tenant $tenant)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to MeatShop Central')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your tenant has been successfully created.')
            ->line('Business: ' . $this->tenant->business_name)
            ->line('Domain: ' . ($this->tenant->domain ?: 'Not assigned'))
            ->line('Plan: ' . ucfirst((string) ($this->tenant->plan ?: 'basic')))
            ->action('Open Tenant', 'http://' . $this->tenant->domain . ':8000/login?force_login=1')
            ->line('Thank you for using MeatShop Central.');
    }
}
