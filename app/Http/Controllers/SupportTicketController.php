<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\UpdateLog;
use App\Services\VersionManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $tickets = SupportTicket::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->paginate(20);

        return view('tenant.support_index', ['tickets' => $tickets]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'message' => 'required|string|min:5|max:2000'
        ]);

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $lastLog = $tenantId ? UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first() : null;

        $ticket = SupportTicket::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'version' => $lastLog->to_version ?? VersionManagementService::getCurrentVersion(),
            'last_update_at' => $lastLog->created_at ?? null,
            'message' => $request->input('message'),
            'status' => 'open',
        ]);

        return redirect()->back()->with('success', 'Issue reported. Support will follow up.');
    }
}
