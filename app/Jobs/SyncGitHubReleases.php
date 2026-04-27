<?php

namespace App\Jobs;

use App\Services\GitHubService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGitHubReleases implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $result = GitHubService::syncReleases();
            
            if ($result['success']) {
                Log::info('GitHub releases auto-synced', [
                    'synced' => $result['synced'],
                    'updated' => $result['updated'],
                    'total' => $result['total_releases']
                ]);
            } else {
                Log::error('GitHub releases auto-sync failed', [
                    'errors' => $result['errors']
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GitHub releases auto-sync job failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
