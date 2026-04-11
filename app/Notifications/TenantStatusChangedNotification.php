<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantStatusChangedNotification extends Notification implements ShouldQueue
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
            ->subject('Tenant Status Updated')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your tenant status has been updated.')
            ->line('Status: ' . ucfirst((string) $this->tenant->status))
            ->line('Payment Status: ' . ucfirst((string) $this->tenant->payment_status))
            ->line('Message: ' . ($this->tenant->disabled_message ?? $this->tenant->suspended_message ?? 'No additional message.'));
    }
}
