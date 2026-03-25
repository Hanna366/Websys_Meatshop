@extends('layouts.central')

@section('title', 'Billing History')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 mb-0">Billing History</h1>
        <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-sm">View Plans</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if (empty($billingHistory))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-invoice-dollar fa-2x mb-3"></i>
                    <p class="mb-0">No billing records found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billingHistory as $entry)
                                <tr>
                                    <td>{{ $entry['id'] ?? '-' }}</td>
                                    <td>{{ $entry['date'] ?? '-' }}</td>
                                    <td>{{ $entry['plan'] ?? '-' }}</td>
                                    <td>${{ $entry['amount'] ?? '-' }}</td>
                                    <td>
                                        @php
                                            $status = $entry['status'] ?? 'Unknown';
                                            $badgeClass = strtolower($status) === 'paid' ? 'bg-success' : 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>{{ $entry['payment_method'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
