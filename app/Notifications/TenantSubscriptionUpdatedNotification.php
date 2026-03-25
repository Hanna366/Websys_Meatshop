<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSubscriptionUpdatedNotification extends Notification implements ShouldQueue
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
        $subscription = is_array($this->tenant->subscription) ? $this->tenant->subscription : [];

        return (new MailMessage)
            ->subject('Subscription Updated')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your subscription has been updated.')
            ->line('Plan: ' . ucfirst((string) ($this->tenant->plan ?: 'basic')))
            ->line('Status: ' . ucfirst((string) ($subscription['status'] ?? 'active')))
            ->line('Billing Cycle: ' . ucfirst((string) ($subscription['billing_cycle'] ?? 'monthly')))
            ->line('Current Period: ' . (($subscription['current_period_start'] ?? '-') . ' to ' . ($subscription['current_period_end'] ?? '-')));
    }
}
