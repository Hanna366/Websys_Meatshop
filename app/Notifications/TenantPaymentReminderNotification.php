<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantPaymentReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Reminder')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your tenant account currently has an unpaid or overdue balance.')
            ->line('Payment Status: ' . ucfirst((string) ($this->tenant->payment_status ?: 'unpaid')))
            ->line('Please settle your balance to avoid service interruption.');
    }
}
