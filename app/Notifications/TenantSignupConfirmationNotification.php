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
        $rawDomain = trim((string) ($this->tenant->domain ?? ''));
        $normalizedDomain = preg_replace('#^https?://#i', '', $rawDomain);
        $normalizedDomain = rtrim((string) $normalizedDomain, '/');

        $appUrl = (string) config('app.url');
        $defaultScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: (app()->environment('production') ? 'https' : 'http');

        if ($normalizedDomain !== '') {
            $hasPort = preg_match('/:\\d+$/', $normalizedDomain) === 1;
            $needsLocalPort = app()->environment('local') && !$hasPort && (str_ends_with($normalizedDomain, '.localhost') || $normalizedDomain === 'localhost');
            $tenantPort = $needsLocalPort ? ':8000' : '';
            $tenantLoginUrl = $defaultScheme . '://' . $normalizedDomain . $tenantPort . '/login?force_login=1';
        } else {
            $baseUrl = rtrim($appUrl, '/');
            $tenantLoginUrl = ($baseUrl !== '' ? $baseUrl : ($defaultScheme . '://localhost')) . '/login';
        }

        return (new MailMessage)
            ->subject('Welcome to MeatShop Central')
            ->greeting('Hello ' . ($this->tenant->admin_name ?: $this->tenant->business_name) . ',')
            ->line('Your tenant has been successfully created.')
            ->line('Business: ' . $this->tenant->business_name)
            ->line('Domain: ' . ($this->tenant->domain ?: 'Not assigned'))
            ->line('Plan: ' . ucfirst((string) ($this->tenant->plan ?: 'basic')))
            ->action('Open Tenant', $tenantLoginUrl)
            ->line('Thank you for using MeatShop Central.');
    }
}
