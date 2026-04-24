<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TempUpdateRequestSeeder extends Seeder
{
    public function run()
    {
        $r = \App\Models\UpdateRequest::create([
            'tenant_id' => 'b97baebf-2350-41c4-9688-ee9caa77da85',
            'user_id' => 9,
            'current_version' => '1.0.0',
            'requested_version' => '1.0.6',
            'status' => 'pending',
        ]);

        echo "Created UpdateRequest id: {$r->id}\n";
    }
}
