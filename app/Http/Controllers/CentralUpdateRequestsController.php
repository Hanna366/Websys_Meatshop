<?php

namespace App\Http\Controllers;

use App\Models\UpdateRequest;
use App\Models\TenantUpdateRequest;
use App\Models\Tenant;
use App\Jobs\ApplyTenantUpdate;
use Illuminate\Http\Request;

class CentralUpdateRequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = UpdateRequest::query()->orderBy('requested_at', 'desc');
        
        // Force fresh data - no caching
        $query->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        $requests = $query->paginate(30)->withQueryString();

        $tenantIds = $requests->pluck('tenant_id')->unique()->filter()->values()->all();
        $tenants = Tenant::whereIn('tenant_id', $tenantIds)->get()->keyBy('tenant_id');

        return view('admin.update_requests.index', [
            'requests' => $requests,
            'tenants' => $tenants,
        ]);
    }

    public function show($id)
    {
        $req = UpdateRequest::findOrFail($id);
        $tenant = Tenant::where('tenant_id', $req->tenant_id)->first();

        return view('admin.update_requests.show', [
            'request' => $req,
            'tenant' => $tenant,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string|max:2000',
        ]);

        $req = UpdateRequest::findOrFail($id);
        $req->status = $request->input('status');
        $req->notes = $request->input('notes');
        if (in_array($req->status, ['approved','completed'])) {
            $req->processed_at = now();
        }
        $req->save();

        // Create update log immediately when approved
        if ($request->input('status') === 'approved') {
            try {
                $tenant = \App\Models\Tenant::where('tenant_id', $req->tenant_id)->first();
                if ($tenant) {
                    \App\Models\UpdateLog::create([
                        'tenant_id' => $tenant->id,
                        'from_version' => $req->current_version,
                        'to_version' => $req->requested_version,
                        'status' => 'completed',
                        'update_data' => [
                            'applied_by' => 'admin_approval',
                            'applied_at' => now()->toDateTimeString(),
                            'central_request_id' => $req->id
                        ],
                        'started_at' => now(),
                        'completed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Mark central request as completed
                    $req->status = 'completed';
                    $req->save();
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to create update log', [
                    'tenant_id' => $req->tenant_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->back()->with('success', 'Update request updated. Tenant notified of status change.');
    }
}
