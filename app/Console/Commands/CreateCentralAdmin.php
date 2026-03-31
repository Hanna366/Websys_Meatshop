<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Console\Command;

class CreateCentralAdmin extends Command
{
    protected $signature = 'admin:create {--force : Force recreate if exists}';
    protected $description = 'Create central admin user';

    public function handle()
    {
        $email = 'admin@meatshop.local';
        $password = 'admin123';
        
        $existingAdmin = User::where('email', $email)->first();
        
        if ($existingAdmin && !$this->option('force')) {
            $this->error("Admin user already exists with email: {$email}");
            $this->info("Use --force to recreate it");
            return 1;
        }
        
        if ($existingAdmin && $this->option('force')) {
            $existingAdmin->delete();
            $this->info("Existing admin user deleted.");
        }
        
        $admin = new User();
        $admin->username = 'admin';
        $admin->name = 'System Administrator'; // Add this line
        $admin->email = $email;
        $admin->password = Hash::make($password);
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->profile = [
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'full_name' => 'System Administrator',
        ];
        $admin->permissions = [
            'can_manage_users' => true,
            'can_manage_inventory' => true,
            'can_process_sales' => true,
            'can_view_reports' => true,
            'can_manage_suppliers' => true,
            'can_manage_customers' => true,
            'can_export_data' => true,
            'can_access_api' => true,
        ];
        $admin->preferences = [
            'language' => 'en',
            'timezone' => 'America/New_York',
            'theme' => 'light',
            'email_notifications' => true,
            'sms_notifications' => false,
        ];
        
        $admin->save();
        
        $this->info("✅ Central admin user created successfully!");
        $this->line("Email: {$email}");
        $this->line("Username: admin");
        $this->line("Password: {$password}");
        $this->line("Login URL: http://localhost:8000/login");
        
        return 0;
    }
}
