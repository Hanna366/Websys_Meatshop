<?php

namespace App\Console\Commands;

use App\Services\TenantService;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create
                            {--name= : Business name}
                            {--email= : Business email}
                            {--phone= : Business phone}
                            {--address= : Business address}
                            {--plan=basic : Subscription plan (basic|standard|premium|enterprise)}
                            {--domain= : Explicit tenant domain (e.g. ramcar.localhost)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a tenant and provision its database.';

    public function handle()
    {
        $name = $this->option('name') ?: $this->ask('Business name');
        $email = $this->option('email') ?: $this->ask('Business email');
        $plan = $this->option('plan') ?: 'basic';

        $data = [
            'business_name' => $name,
            'business_email' => $email,
            'business_phone' => $this->option('phone'),
            'business_address' => $this->option('address'),
            'plan' => $plan,
        ];

        if ($this->option('domain')) {
            $data['domain'] = $this->option('domain');
        }

        $tenant = TenantService::createTenant($data);

        $this->info('Tenant created successfully!');
        $this->line('Tenant ID: ' . $tenant->tenant_id);
        $this->line('Domain: ' . $tenant->domain);
        $this->line('DB Name: ' . $tenant->db_name);
        $this->line('DB Username: ' . $tenant->db_username);
        $this->line('DB Password: ' . $tenant->getDecryptedDbPassword());

        $this->line('Add an entry to your hosts file and visit:');
        $this->line('  http://' . $tenant->domain . ':8000');

        return 0;
    }
}
