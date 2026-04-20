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

        try {
            $releases = $github->fetchReleases();

            $latest = collect($releases)
                ->first(function (array $release): bool {
                    if (! empty($release['draft'])) {
                        return false;
                    }

                    if (! env('GITHUB_INCLUDE_PRERELEASE', false) && ! empty($release['prerelease'])) {
                        return false;
                    }

                    return true;
                });

            if (! $latest) {
                $latest = collect($releases)->first();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to fetch latest release for index view', ['message' => $e->getMessage()]);
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
}
