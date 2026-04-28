<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\UserCredentialsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantUserController extends Controller
{
    /**
     * Ensure tenant database connection is properly configured
     */
    private function ensureTenantConnection(): void
    {
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            if (method_exists($tenant, 'getTenantDatabaseConfig')) {
                config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
                DB::purge('tenant');
            }
        }
    }

    /**
     * Display list of users (owner/manager only)
     */
    public function index()
    {
        $this->ensureTenantConnection();
        
        $users = User::on('tenant')->where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenant.users.index', compact('users'));
    }

    /**
     * Show form to create new user (owner/manager only)
     */
    public function create()
    {
        // Only admin can create users
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to create users.');
        }

        $roles = [
            'cashier' => 'Cashier (Sales + View Inventory Only)',
            'admin' => 'Admin (Full Access)',
        ];

        return view('tenant.users.create', compact('roles'));
    }

    /**
     * Store new user and show credentials (admin only)
     */
    public function store(Request $request)
    {
        $this->ensureTenantConnection();
        
        // Only admin can create users
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to create users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:cashier,admin',
        ]);

        // Generate random password
        $password = Str::random(10);

        // Generate username from email
        $username = explode('@', $request->email)[0];
        $baseUsername = $username;
        $counter = 1;
        
        // Ensure username is unique in tenant database
        while (User::on('tenant')->where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::on('tenant')->create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($password),
            'role' => $request->role,
            'status' => 'active',
            'profile' => json_encode([]),
            'permissions' => json_encode([
                'can_manage_users' => $request->role === 'admin',
                'can_manage_inventory' => $request->role === 'admin',
                'can_process_sales' => true,
                'can_view_reports' => $request->role === 'admin',
                'can_manage_suppliers' => $request->role === 'admin',
                'can_manage_customers' => true,
                'can_export_data' => $request->role === 'admin',
                'can_access_api' => false,
            ]),
            'login_attempts' => 0,
        ]);

        // Get tenant info for email
        $tenant = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
        }
        $tenantName = $tenant?->business_name ?? config('app.name', 'Meat Shop POS');
        
        // Build login and reset URLs
        $scheme = request()->getScheme();
        $host = request()->getHost();
        $port = request()->getPort();
        $portPart = ($port && $port !== 80 && $port !== 443) ? ':' . $port : '';
        $loginUrl = "{$scheme}://{$host}{$portPart}/login";
        $resetUrl = "{$scheme}://{$host}{$portPart}/forgot-password";

        // Send email with credentials
        try {
            Mail::to($user->email)->queue(new UserCredentialsMail(
                tenantName: $tenantName,
                userName: $user->name,
                userEmail: $user->email,
                username: $username,
                role: $user->role,
                generatedPassword: $password,
                loginUrl: $loginUrl,
                resetUrl: $resetUrl
            ));
            
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::warning('User credentials email could not be sent.', [
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            $emailSent = false;
        }

        // Store credentials in session to show in notification (still show on page as backup)
        $credentials = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $username,
            'password' => $password,
            'role' => $user->role,
            'email_sent' => $emailSent,
        ];

        $message = $emailSent 
            ? 'User created successfully. Credentials have been sent to their email.'
            : 'User created successfully. Please provide the credentials below to the user.';

        return redirect('/users')
            ->with('success', $message)
            ->with('new_user_credentials', $credentials);
    }

    /**
     * Display user details
     */
    public function show($id)
    {
        $this->ensureTenantConnection();
        $user = User::on('tenant')->findOrFail($id);
        return view('tenant.users.show', compact('user'));
    }

    /**
     * Show edit form (admin can edit all users)
     */
    public function edit($id)
    {
        $this->ensureTenantConnection();
        $user = User::on('tenant')->findOrFail($id);
        
        // Cashier cannot edit users
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to edit users.');
        }

        $roles = [
            'cashier' => 'Cashier (Sales + View Inventory Only)',
            'admin' => 'Admin (Full Access)',
        ];

        return view('tenant.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $user = User::on('tenant')->findOrFail($id);
        
        // Cashier cannot edit users
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to edit users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:cashier,admin',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect('/users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Reset user password (owner/manager only)
     */
    public function resetPassword($id)
    {
        $this->ensureTenantConnection();
        $user = User::on('tenant')->findOrFail($id);
        
        // Cashier cannot reset passwords
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to reset passwords.');
        }

        // Generate new password
        $password = Str::random(10);
        
        $user->update([
            'password' => Hash::make($password),
        ]);

        // Get tenant info for email
        $tenant = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
        }
        $tenantName = $tenant?->business_name ?? config('app.name', 'Meat Shop POS');
        
        // Build login and reset URLs
        $scheme = request()->getScheme();
        $host = request()->getHost();
        $port = request()->getPort();
        $portPart = ($port && $port !== 80 && $port !== 443) ? ':' . $port : '';
        $loginUrl = "{$scheme}://{$host}{$portPart}/login";
        $resetUrl = "{$scheme}://{$host}{$portPart}/forgot-password";

        // Send email with new credentials
        try {
            Mail::to($user->email)->queue(new UserCredentialsMail(
                tenantName: $tenantName,
                userName: $user->name,
                userEmail: $user->email,
                username: $user->username,
                role: $user->role,
                generatedPassword: $password,
                loginUrl: $loginUrl,
                resetUrl: $resetUrl
            ));
            
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::warning('Password reset email could not be sent.', [
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            $emailSent = false;
        }

        $credentials = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'password' => $password,
            'role' => $user->role,
            'email_sent' => $emailSent,
        ];

        $message = $emailSent 
            ? 'Password reset successfully. New credentials have been sent to their email.'
            : 'Password reset successfully. Please provide the new password below to the user.';

        return redirect('/users')
            ->with('success', $message)
            ->with('reset_credentials', $credentials);
    }

    /**
     * Delete user (admin only)
     */
    public function destroy($id)
    {
        $this->ensureTenantConnection();
        $user = User::on('tenant')->findOrFail($id);
        
        // Cashier cannot delete users
        if (auth()->user()->role === 'cashier') {
            return redirect('/dashboard')
                ->with('error', 'You do not have permission to delete users.');
        }

        // Cannot delete own account
        if ($user->id === auth()->id()) {
            return redirect('/users')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->update(['status' => 'deleted']);

        return redirect('/users')
            ->with('success', 'User deleted successfully.');
    }
}
