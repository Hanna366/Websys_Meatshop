<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Set is_stable/is_deprecated/is_available_to_tenants based on status
        DB::table('versions')->orderBy('release_date', 'asc')->chunk(100, function ($rows) {
            foreach ($rows as $r) {
                $isStable = ($r->status === 'stable');
                $isDeprecated = ($r->status === 'deprecated');
                $isAvailable = $isStable; // default: stable releases are available to tenants

                DB::table('versions')->where('id', $r->id)->update([
                    'is_stable' => $isStable,
                    'is_deprecated' => $isDeprecated,
                    'is_available_to_tenants' => $isAvailable,
                ]);
            }
        });
    }

    public function down(): void
    {
        // no-op
    }
};
