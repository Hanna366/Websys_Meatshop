<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CentralTenantApprovalRequestNotification extends Notification implements ShouldQueue
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
        $tenant = $this->tenant;

        $approvalUrl = route('tenants.show', $tenant->tenant_id);

        return (new MailMessage)
            ->subject('Tenant Approval Requested: ' . ($tenant->business_name ?? $tenant->tenant_id))
            ->greeting('Hello,')
            ->line('A new tenant has signed up and requires your approval before provisioning.')
            ->line('Business: ' . ($tenant->business_name ?? '—'))
            ->line('Admin: ' . ($tenant->admin_name ?? '—') . ' <' . ($tenant->admin_email ?? '—') . '>')
            ->action('Review Tenant', $approvalUrl)
            ->line('Approve the tenant to provision its database and admin account.');
    }
}
