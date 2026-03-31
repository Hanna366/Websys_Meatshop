<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckSystem extends Command
{
    protected $signature = 'system:check';
    protected $description = 'Check admin user and tenants';

    public function handle()
    {
        $this->info('=== System Check ===');
        
        // Check admin user
        $this->info("\n1. Checking Admin User:");
        $admin = User::where('email', 'admin@meatshop.local')->first();
        
        if ($admin) {
            $this->info("✅ Admin user found:");
            $this->line("   - Email: {$admin->email}");
            $this->line("   - Role: {$admin->role}");
            $this->line("   - Tenant ID: " . ($admin->tenant_id ?? 'NULL'));
            $this->line("   - ID: {$admin->id}");
            $this->line("   - Status: {$admin->status}");
            
            $isCentralUser = empty($admin->tenant_id);
            $isPrivilegedRole = in_array(strtolower($admin->role), ['owner', 'admin', 'administrator', 'super_admin', 'superadmin'], true);
            
            if ($isCentralUser && $isPrivilegedRole) {
                $this->info("✅ Admin has central admin permissions");
            } else {
                $this->error("❌ Admin lacks central admin permissions:");
                if (!$isCentralUser) {
                    $this->error("   - Has tenant_id (should be NULL for central admin)");
                }
                if (!$isPrivilegedRole) {
                    $this->error("   - Role not in allowed list");
                }
            }
        } else {
            $this->error("❌ Admin user not found!");
            $this->line("Run: php artisan admin:create");
        }
        
        // Check tenants
        $this->info("\n2. Checking Tenants:");
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->error("❌ No tenants found in database!");
        } else {
            $this->info("✅ Found {$tenants->count()} tenant(s):");
            
            foreach ($tenants as $tenant) {
                $this->line("   - {$tenant->business_name} ({$tenant->domain})");
                $this->line("     Email: {$tenant->business_email}");
                $this->line("     Plan: {$tenant->plan}");
                $this->line("     Status: {$tenant->status}");
                $this->line("     Tenant UUID: {$tenant->tenant_id}");
                $this->line("");
            }
            
            // Look for buksu specifically
            $buksuTenants = $tenants->filter(function ($tenant) {
                return str_contains(strtolower($tenant->business_name), 'buksu') || 
                       str_contains(strtolower($tenant->domain), 'buksu');
            });
            
            if ($buksuTenants->isNotEmpty()) {
                $this->info("✅ Found buksu tenant(s):");
                foreach ($buksuTenants as $tenant) {
                    $this->line("   - {$tenant->domain} ({$tenant->business_name})");
                }
            } else {
                $this->error("❌ No 'buksu' tenants found!");
            }
        }
        
        $this->info("\n=== System Check Complete ===");
        
        return 0;
    }
}
