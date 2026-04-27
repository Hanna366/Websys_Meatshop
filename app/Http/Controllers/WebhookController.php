<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGitHubReleases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function github(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        
        // Verify webhook signature (optional but recommended)
        $secret = config('services.github.webhook_secret');
        if ($secret && $signature) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Invalid GitHub webhook signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }
        
        $event = $request->header('X-GitHub-Event');
        
        // Handle release events
        if ($event === 'release') {
            $releaseData = $request->json();
            
            if (($releaseData['action'] ?? '') === 'published') {
                Log::info('New GitHub release published', [
                    'tag' => $releaseData['release']['tag_name'] ?? 'unknown',
                    'name' => $releaseData['release']['name'] ?? 'unknown'
                ]);
                
                // Queue immediate sync
                SyncGitHubReleases::dispatch();
                
                return response()->json(['status' => 'processed']);
            }
        }
        
        // Handle push events to main/master (might indicate new release)
        if ($event === 'push') {
            $pushData = $request->json();
            $ref = $pushData['ref'] ?? '';
            
            if (in_array($ref, ['refs/heads/main', 'refs/heads/master'])) {
                Log::info('Push to main branch, checking for releases');
                SyncGitHubReleases::dispatch();
            }
        }
        
        return response()->json(['status' => 'ok']);
    }
}
