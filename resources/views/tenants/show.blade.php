@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tenant Details</h1>
        <a href="/tenants" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Basic Info</h5>
                    <p><strong>Name:</strong> {{ $tenant->business_name }}</p>
                    <p><strong>Domain:</strong> {{ $tenant->domain }}</p>
                    <p><strong>Plan:</strong> {{ ucfirst($tenant->plan) }}</p>
                    <p><strong>Plan Start:</strong> {{ optional($tenant->plan_started_at)->format('Y-m-d') ?? '—' }}</p>
                    <p><strong>Plan End:</strong> {{ optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Access Info</h5>
                    <p><strong>Admin:</strong> {{ $tenant->admin_name ?? '—' }}</p>
                    <p><strong>Email:</strong> {{ $tenant->admin_email ?? $tenant->business_email }}</p>
                    <p><strong>Tenant DB:</strong> {{ $tenant->db_name }}</p>
                    <p><strong>Tenant Domain:</strong> <code>{{ $tenant->domain }}</code></p>

                    <div class="alert alert-info mt-3">
                        <h6>Localhost Setup</h6>
                        <p>To access this tenant locally, add a hosts entry such as:</p>
                        <pre>127.0.0.1 {{ $tenant->domain }}</pre>
                        <p>Then visit <strong>http://{{ $tenant->domain }}:8000</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
