<?php

namespace App\Services;

use App\Models\Tenant;
use App\Notifications\TenantExpirationAlertNotification;
use App\Notifications\TenantPaymentReminderNotification;
use App\Notifications\TenantSignupConfirmationNotification;
use App\Notifications\TenantStatusChangedNotification;
use App\Notifications\TenantSubscriptionUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    private function tenantRecipientEmail(Tenant $tenant): ?string
    {
        return $tenant->admin_email ?: $tenant->business_email;
    }

    public function sendTenantSignupConfirmation(Tenant $tenant): void
    {
        $recipient = $this->tenantRecipientEmail($tenant);
        if (!$recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new TenantSignupConfirmationNotification($tenant));
    }

    public function sendTenantStatusChanged(Tenant $tenant): void
    {
        $recipient = $this->tenantRecipientEmail($tenant);
        if (!$recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new TenantStatusChangedNotification($tenant));
    }

    public function sendSubscriptionUpdated(Tenant $tenant): void
    {
        $recipient = $this->tenantRecipientEmail($tenant);
        if (!$recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new TenantSubscriptionUpdatedNotification($tenant));
    }

    public function sendPaymentReminder(Tenant $tenant): void
    {
        $recipient = $this->tenantRecipientEmail($tenant);
        if (!$recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new TenantPaymentReminderNotification($tenant));
    }

    public function sendExpirationAlert(Tenant $tenant): void
    {
        $recipient = $this->tenantRecipientEmail($tenant);
        if (!$recipient) {
            return;
        }

        Notification::route('mail', $recipient)->notify(new TenantExpirationAlertNotification($tenant));
    }

    public function settings(string $tenantId): array
    {
        return session("notification_settings.{$tenantId}", [
            'email_enabled' => true,
            'sms_enabled' => false,
            'low_stock_threshold' => 10,
            'expiry_warning_days' => 7,
        ]);
    }

    public function updateSettings(string $tenantId, array $payload): array
    {
        $current = $this->settings($tenantId);
        $updated = array_merge($current, $payload);

        session(["notification_settings.{$tenantId}" => $updated]);

        return $updated;
    }

    public function lowStockMessage(array $payload): array
    {
        return [
            'type' => 'low_stock',
            'queued' => true,
            'payload' => $payload,
            'queued_at' => now()->toIso8601String(),
        ];
    }

    public function expiryMessage(array $payload): array
    {
        return [
            'type' => 'expiry',
            'queued' => true,
            'payload' => $payload,
            'queued_at' => now()->toIso8601String(),
        ];
    }

    public function customerMessage(array $payload): array
    {
        return [
            'type' => 'customer',
            'queued' => true,
            'payload' => $payload,
            'queued_at' => now()->toIso8601String(),
        ];
    }
}