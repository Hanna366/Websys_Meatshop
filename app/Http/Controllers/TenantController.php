<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Domain;
use App\Services\NotificationService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class TenantController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(Request $request)
    {
        // Debug: record incoming query string for troubleshooting UI filter issues.
        try {
            $qs = (string) $request->getQueryString();
            if ($qs !== null) {
                file_put_contents(storage_path('logs/tenant_filter_debug.log'), date('c') . " " . $qs . "\n", FILE_APPEND | LOCK_EX);
            }
        } catch (\Throwable $e) {
            // swallow errors to avoid disrupting normal flow
        }

        $query = Tenant::query();

        if ($request->filled('q')) {
            $term = (string) $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->where('tenant_id', 'like', "%{$term}%")
                    ->orWhere('business_name', 'like', "%{$term}%")
                    ->orWhere('business_email', 'like', "%{$term}%")
                    ->orWhere('domain', 'like', "%{$term}%")
                    ->orWhere('admin_name', 'like', "%{$term}%")
                    ->orWhere('admin_email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $status = (string) $request->input('status');
            // When the UI selects "disabled", return only tenants whose status is
            // explicitly 'disabled' (do not include legacy 'suspended' rows).
            if ($status === 'disabled') {
                $query->where('status', 'disabled');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('plan')) {
            $query->where('plan', (string) $request->input('plan'));
        }

        $tenants = $query->orderBy('created_at', 'desc')->get();

        // Debug: log count and a short summary of returned tenants to help
        // diagnose why the UI might still display all rows.
        try {
            $summary = $tenants->take(10)->map(function ($t) {
                return ($t->tenant_id ?? substr((string) $t->business_name, 0, 24)) . '|' . ($t->status ?? '');
            })->implode(',');
            file_put_contents(storage_path('logs/tenant_filter_debug.log'), date('c') . " RESULT count=" . $tenants->count() . " rows=" . $summary . "\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // ignore
        }

        return view('tenants.index', [
            'tenants' => $tenants,
        ]);
    }

    public function show(string $tenantId)
    {
        $tenant = Tenant::where('tenant_id', $tenantId)
            ->orWhere('id', $tenantId)
            ->firstOrFail();

        return view('tenants.show', [
            'tenant' => $tenant,
        ]);
    }

    public function create()
    {
        return view('account.create', [
            // 'showRecaptcha' => $this->shouldEnableRecaptcha(request()),
            'showRecaptcha' => false, // Temporarily disabled
        ]);
    }

    public function store(Request $request)
    {
        // $recaptchaEnabled = $this->shouldEnableRecaptcha($request);
        $recaptchaEnabled = false; // Temporarily disabled

        $request->merge([
            'domain' => $this->normalizeDomain($request->input('domain')),
        ]);

        $rules = [
            'business_name' => 'required|string|max:255',
            'business_email' => ['required', 'email', 'max:255', Rule::unique('tenants', 'business_email')],
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:1000',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->whereNull('deleted_at')],
            'password' => 'nullable|string|min:8',
        ];

        // if ($recaptchaEnabled) {
        //     $rules['g-recaptcha-response'] = 'required|string';
        // }

        $validated = $request->validate($rules, [
            'business_email.unique' => 'A tenant with this business email already exists.',
            // 'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);

        // if ($recaptchaEnabled) {
        //     $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        //         'secret' => (string) config('services.recaptcha.secret_key'),
        //         'response' => (string) $request->input('g-recaptcha-response'),
        //         'remoteip' => $request->ip(),
        //     ]);

        //     if (!$recaptchaResponse->successful() || !data_get($recaptchaResponse->json(), 'success', false)) {
        //         return back()
        //             ->withErrors(['captcha' => 'reCAPTCHA verification failed. Please try again.'])
        //             ->withInput();
        //     }
        // }

        $domain = $validated['domain'] ?? null;

        // Create a pending tenant record in central. Provisioning (DB, admin user)
        // will occur only after central approval.
        $tenant = Tenant::create([
            'tenant_id' => Str::uuid()->toString(),
            'business_name' => $validated['business_name'],
            'business_email' => $validated['business_email'],
            'business_phone' => $validated['business_phone'] ?? null,
            'business_address' => $validated['business_address'] ?? null,
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'plan' => $validated['plan'],
            'domain' => $domain,
            'status' => 'pending',
            'subscription' => [
                'plan' => $validated['plan'],
                'status' => 'pending',
            ],
            // Provide defaults for non-nullable JSON columns to avoid DB errors.
            'settings' => [],
            'usage' => [],
            'limits' => [],
        ]);

        // Ensure domains table maps the tenant -> domain for Stancl tenancy domain resolution
        if (!empty($domain) && Schema::hasTable('domains')) {
            Domain::firstOrCreate([
                'domain' => $domain,
            ], [
                'tenant_id' => $tenant->id,
            ]);
        }

        // Notify tenant (signup confirmation) and request central approval.
        $this->notificationService->sendTenantSignupConfirmation($tenant);
        $this->notificationService->sendCentralApprovalRequest($tenant);

        return redirect()->route('tenants.show', $tenant->tenant_id)
            ->with('success', 'Tenant signup submitted and is pending central approval.');
    }

    public function update(Request $request, string $tenantId)
    {
        $tenant = Tenant::where('tenant_id', $tenantId)
            ->orWhere('id', $tenantId)
            ->firstOrFail();

        $request->merge([
            'domain' => $this->normalizeDomain($request->input('domain')),
        ]);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => ['required', 'email', 'max:255', Rule::unique('tenants', 'business_email')->ignore($tenant->id)],
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:1000',
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|email|max:255',
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($tenant->id)->whereNull('deleted_at')],
        ]);

        TenantService::updateTenantProfile($tenantId, [
            'business_name' => $validated['business_name'],
            'business_email' => $validated['business_email'],
            'business_phone' => $validated['business_phone'] ?? null,
            'business_address' => $validated['business_address'] ?? null,
            'admin_name' => $validated['admin_name'] ?? null,
            'admin_email' => $validated['admin_email'] ?? null,
            'domain' => $validated['domain'] ?? null,
        ]);

        return redirect()->route('tenants.show', $tenantId)
            ->with('success', 'Tenant profile updated.');
    }

    public function updateStatus(Request $request, string $tenantId)
    {
        $tenant = Tenant::where('tenant_id', $tenantId)
            ->orWhere('id', $tenantId)
            ->firstOrFail();

        $previousStatus = $tenant->status;

        $request->merge([
            'domain' => $this->normalizeDomain($request->input('domain')),
        ]);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,disabled,unpaid',
            'payment_status' => 'nullable|in:paid,unpaid,overdue',
            'suspended_message' => 'nullable|string|max:500',
            'disabled_message' => 'nullable|string|max:500',
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($tenant->id)->whereNull('deleted_at'),
            ],
        ]);

        $domain = $validated['domain'] ?? null;

        TenantService::updateTenantLifecycle($tenantId, [
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? ($validated['status'] === 'unpaid' ? 'unpaid' : 'paid'),
            'suspended_message' => $validated['suspended_message'] ?? ($validated['disabled_message'] ?? 'Please contact your administrator.'),
            'disabled_message' => $validated['disabled_message'] ?? ($validated['suspended_message'] ?? 'Please contact your administrator.'),
            'domain' => $domain,
        ]);

        $tenant->refresh();

        // If tenant was pending and is now being activated, queue provisioning
        // to avoid long-running web requests that can exceed PHP's execution time.
        if ($previousStatus === 'pending' && ($validated['status'] ?? '') === 'active') {
            try {
                \App\Jobs\ProvisionTenantJob::dispatch($tenant->tenant_id, true);
                session()->flash('tenant_provisioning_queued', true);
            } catch (\Throwable $e) {
                \Log::error('Failed to dispatch provisioning job during approval.', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);
            }
        }

        $this->notificationService->sendTenantStatusChanged($tenant);

        if (($validated['payment_status'] ?? null) === 'unpaid' || $validated['status'] === 'unpaid') {
            $this->notificationService->sendPaymentReminder($tenant);
        }

        return redirect()->route('tenants.show', $tenantId)
            ->with('success', 'Tenant status updated.');
    }

    public function updateSubscription(Request $request, string $tenantId)
    {
        $validated = $request->validate([
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'billing_cycle' => 'required|in:monthly,annual',
            'subscription_status' => 'required|in:active,unpaid,expired,cancelled',
            'payment_status' => 'nullable|in:paid,unpaid,overdue',
            'tenant_status' => 'nullable|in:active,inactive,suspended,unpaid',
            'current_period_start' => 'nullable|date',
            'current_period_end' => 'nullable|date|after_or_equal:current_period_start',
        ]);

        // Resolve tenant by UUID or numeric id before updating subscription
        $tenantRecord = Tenant::where('tenant_id', $tenantId)->orWhere('id', $tenantId)->firstOrFail();
        $tenant = TenantService::updateTenantSubscription($tenantRecord->tenant_id ?? $tenantRecord->id, $validated);

        $this->notificationService->sendSubscriptionUpdated($tenant);

        if (($validated['subscription_status'] ?? 'active') === 'expired') {
            $this->notificationService->sendExpirationAlert($tenant);
        }

        if (($validated['payment_status'] ?? null) === 'unpaid' || ($validated['subscription_status'] ?? null) === 'unpaid') {
            $this->notificationService->sendPaymentReminder($tenant);
        }

        return redirect()->route('tenants.show', $tenantId)
            ->with('success', 'Tenant subscription updated.');
    }

    private function normalizeDomain(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $normalized = trim($domain);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('#^https?://#i', '', $normalized);
        $normalized = rtrim((string) $normalized, '/');

        return str_ireplace(['locasthost', 'locathost'], 'localhost', $normalized);
    }

    private function shouldEnableRecaptcha(Request $request): bool
    {
        $siteKey = (string) config('services.recaptcha.site_key');
        $secretKey = (string) config('services.recaptcha.secret_key');

        if ($siteKey === '' || $secretKey === '') {
            return false;
        }

        $host = strtolower((string) $request->getHost());

        $isLocalHost = $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.localhost');

        return !$isLocalHost;
    }
}

