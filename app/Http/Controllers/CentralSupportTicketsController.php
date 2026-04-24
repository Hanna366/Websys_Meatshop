<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CentralSupportTicketsController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::query()->orderBy('created_at', 'desc');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate(30)->withQueryString();

        $tenantIds = $tickets->pluck('tenant_id')->unique()->filter()->values()->all();
        $tenants = Tenant::whereIn('tenant_id', $tenantIds)->get()->keyBy('tenant_id');

        return view('admin.support_tickets.index', [
            'tickets' => $tickets,
            'tenants' => $tenants,
        ]);
    }

    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $tenant = Tenant::where('tenant_id', $ticket->tenant_id)->first();

        return view('admin.support_tickets.show', [
            'ticket' => $ticket,
            'tenant' => $tenant,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved'
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->input('status');
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket status updated.');
    }
}
