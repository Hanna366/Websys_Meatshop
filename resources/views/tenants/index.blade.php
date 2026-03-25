@extends('layouts.central')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tenants</h1>
        <a href="/account/create" class="btn btn-primary">Create New Tenant</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tenant ID</th>
                            <th>Tenant</th>
                            <th>Address</th>
                            <th>Domain</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Plan Start</th>
                            <th>Plan End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->tenant_id }}</td>
                                <td>{{ $tenant->business_name }}</td>
                                <td>{{ is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : $tenant->business_address }}</td>
                                <td>
                                    @if(!empty($tenant->domain))
                                        @php
                                            $rawDomain = trim((string) $tenant->domain);
                                            $normalizedDomain = preg_replace('#^https?://#i', '', $rawDomain);
                                            $normalizedDomain = rtrim($normalizedDomain, '/');
                                            $normalizedDomain = str_ireplace('locasthost', 'localhost', $normalizedDomain);
                                            $scheme = request()->isSecure() ? 'https' : 'http';
                                            $hasPort = preg_match('/:\\d+$/', $normalizedDomain) === 1;
                                            $tenantPort = app()->environment('local') && !$hasPort ? ':8000' : '';
                                            $tenantUrl = $scheme . '://' . $normalizedDomain . $tenantPort . '/login?force_login=1';
                                        @endphp
                                        <a href="{{ $tenantUrl }}" target="_blank" rel="noopener noreferrer">{{ $normalizedDomain }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $tenant->admin_name ?? '—' }}</td>
                                <td>{{ $tenant->admin_email ?? $tenant->business_email }}</td>
                                <td>{{ ucfirst($tenant->plan ?? 'basic') }}</td>
                                <td>{{ ucfirst($tenant->status ?? 'active') }}</td>
                                <td>{{ optional($tenant->plan_started_at)->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <a href="/tenant/{{ $tenant->tenant_id }}" class="btn btn-sm btn-outline-primary">Customize</a>
                                    @if(!empty($tenant->domain))
                                        @php
                                            $rawDomain = trim((string) $tenant->domain);
                                            $normalizedDomain = preg_replace('#^https?://#i', '', $rawDomain);
                                            $normalizedDomain = rtrim($normalizedDomain, '/');
                                            $normalizedDomain = str_ireplace('locasthost', 'localhost', $normalizedDomain);
                                            $scheme = request()->isSecure() ? 'https' : 'http';
                                            $hasPort = preg_match('/:\\d+$/', $normalizedDomain) === 1;
                                            $tenantPort = app()->environment('local') && !$hasPort ? ':8000' : '';
                                            $tenantUrl = $scheme . '://' . $normalizedDomain . $tenantPort . '/login?force_login=1';
                                        @endphp
                                        <a href="{{ $tenantUrl }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer">Open Tenant</a>
                                    @endif
                                    @if(($tenant->status ?? 'active') === 'active')
                                        <form method="POST" action="{{ route('tenants.updateStatus', $tenant->tenant_id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="suspended">
                                            <input type="hidden" name="payment_status" value="overdue">
                                            <input type="hidden" name="suspended_message" value="Please contact your administrator.">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('tenants.updateStatus', $tenant->tenant_id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <input type="hidden" name="payment_status" value="paid">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Enable</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No tenants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
