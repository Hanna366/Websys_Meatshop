<?php

namespace App\Http\Controllers;

use App\Models\SystemUpdate;
use App\Services\GitHubService;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeAdmin()
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $role = strtolower((string) ($user->role ?? ''));
        if (in_array($role, ['owner', 'admin', 'administrator', 'super_admin', 'superadmin'], true)) {
            return true;
        }

        abort(403);
    }

    public function index(GitHubService $github)
    {
        $this->authorizeAdmin();

        $current = env('APP_VERSION') ?: config('app.version') ?: 'unknown';
        $latest = null;

        // Use database versions instead of direct GitHub API call
        try {
            $latestVersion = \App\Models\Version::where('is_stable', true)
                ->where('is_available_to_tenants', true)
                ->orderBy('version', 'desc')
                ->first();

            if ($latestVersion) {
                $latest = [
                    'tag_name' => $latestVersion->version,
                    'name' => $latestVersion->name ?? 'Version ' . $latestVersion->version,
                    'published_at' => $latestVersion->created_at,
                    'body' => $latestVersion->description ?? '',
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Failed to fetch latest version from database', ['message' => $e->getMessage()]);
        }

        $status = SystemUpdate::orderByDesc('created_at')->first();

        return view('admin.update', compact('current', 'latest', 'status'));
    }

    public function update(Request $request, UpdateService $updates)
    {
        $this->authorizeAdmin();

        $request->validate([
            'regenerate_app_key' => 'sometimes|boolean',
            'run_composer_install' => 'sometimes|boolean',
            'run_npm_install' => 'sometimes|boolean',
            'run_migrations' => 'sometimes|boolean',
        ]);

        $result = $updates->downloadAndQueueLatest([
            'regenerate_app_key' => $request->boolean('regenerate_app_key'),
            'run_composer_install' => $request->boolean('run_composer_install', true),
            'run_npm_install' => $request->boolean('run_npm_install', true),
            'run_migrations' => $request->boolean('run_migrations', true),
        ]);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', 'System update queued successfully.');
    }

    public function status()
    {
        $this->authorizeAdmin();

        return response()->json(
            SystemUpdate::orderByDesc('created_at')->first() ?? ['status' => 'idle']
        );
    }

    public function sync()
    {
        $this->authorizeAdmin();

        try {
            $result = GitHubService::syncReleases();
            
            if ($result['success']) {
                $message = "Sync completed: {$result['synced']} new, {$result['updated']} updated releases.";
                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('error', 'Sync failed: ' . implode(', ', $result['errors']));
            }
        } catch (\Throwable $e) {
            Log::error('GitHub sync error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
