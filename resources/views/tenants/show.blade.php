@extends('layouts.central')

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

                    <hr>

                    <h6 class="mb-3">Customize Tenant</h6>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('tenants.updateStatus', $tenant->tenant_id) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Domain</label>
                            <input type="text" name="domain" class="form-control" value="{{ $tenant->domain }}" placeholder="ramcar.localhost">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ ($tenant->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($tenant->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ ($tenant->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="unpaid" {{ ($tenant->status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="paid" {{ ($tenant->payment_status ?? 'paid') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ ($tenant->payment_status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="overdue" {{ ($tenant->payment_status ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Suspension Message</label>
                            <input type="text" name="suspended_message" class="form-control" value="{{ $tenant->suspended_message ?? 'Please contact your administrator.' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
