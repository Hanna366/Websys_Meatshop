@extends('layouts.app')

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
                            <th>Tenant</th>
                            <th>Address</th>
                            <th>Domain</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Plan Start</th>
                            <th>Plan End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->business_name }}</td>
                                <td>{{ is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : $tenant->business_address }}</td>
                                <td>{{ $tenant->domain }}</td>
                                <td>{{ $tenant->admin_name ?? '—' }}</td>
                                <td>{{ $tenant->admin_email ?? $tenant->business_email }}</td>
                                <td>{{ ucfirst($tenant->plan ?? 'basic') }}</td>
                                <td>{{ optional($tenant->plan_started_at)->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <a href="/tenant/{{ $tenant->tenant_id }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
