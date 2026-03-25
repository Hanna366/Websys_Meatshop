<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantExpirationAlertNotification extends Notification implements ShouldQueue
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
        $expiry = optional($this->tenant->plan_ends_at)->format('Y-m-d') ?: 'N/A';

        return (new MailMessage)
            ->subject('Subscription Expiration Alert')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your subscription is near expiry or has expired.')
            ->line('Plan: ' . ucfirst((string) ($this->tenant->plan ?: 'basic')))
            ->line('Plan End Date: ' . $expiry)
            ->line('Please renew to keep tenant access active.');
    }
}
