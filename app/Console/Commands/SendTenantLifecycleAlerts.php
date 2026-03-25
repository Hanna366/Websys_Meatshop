<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTenantLifecycleAlerts extends Command
{
    protected $signature = 'tenant:send-lifecycle-alerts';

    protected $description = 'Send payment reminders and subscription expiration alerts to tenant contacts.';

    public function handle(NotificationService $notificationService): int
    {
        $paymentReminderCount = 0;
        $expirationAlertCount = 0;

        $tenants = Tenant::query()->whereNull('deleted_at')->get();

        foreach ($tenants as $tenant) {
            $subscription = is_array($tenant->subscription) ? $tenant->subscription : [];
            $periodEnd = $subscription['current_period_end'] ?? optional($tenant->plan_ends_at)->toDateString();

            if (in_array((string) $tenant->payment_status, ['unpaid', 'overdue'], true)) {
                $notificationService->sendPaymentReminder($tenant);
                $paymentReminderCount++;
            }

            if ($periodEnd) {
                $daysToExpiry = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse((string) $periodEnd)->startOfDay(), false);
                if ($daysToExpiry <= 7) {
                    $notificationService->sendExpirationAlert($tenant);
                    $expirationAlertCount++;
                }
            }
        }

        $this->info("Payment reminders sent: {$paymentReminderCount}");
        $this->info("Expiration alerts sent: {$expirationAlertCount}");

        return self::SUCCESS;
    }
}
