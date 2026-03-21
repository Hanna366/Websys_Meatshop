<?php

namespace App\Services;

class NotificationService
{
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