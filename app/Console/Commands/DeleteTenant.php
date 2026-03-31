<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class DeleteTenant extends Command
{
    protected $signature = 'tenant:delete {email : Business email of tenant to delete} {--force : Force delete without confirmation}';
    protected $description = 'Delete a tenant by business email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $tenant = Tenant::where('business_email', $email)->first();
        
        if (!$tenant) {
            $this->error("No tenant found with email: {$email}");
            return 1;
        }
        
        $this->info("Found tenant: ID {$tenant->id}, Domain: {$tenant->domain}, Business: {$tenant->business_name}");
        
        // Always delete if force option is used, otherwise ask for confirmation
        $shouldDelete = $this->option('force') || $this->confirm('Are you sure you want to delete this tenant? This will delete ALL data.');
        
        if ($shouldDelete) {
            $tenant->delete();
            $this->info("Tenant deleted successfully!");
            return 0;
        }
        
        $this->info("Deletion cancelled.");
        return 0;
    }
}
