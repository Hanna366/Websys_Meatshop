<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GitHubService;
use App\Services\UpdateService;
use App\Models\SystemUpdate;

class SystemUpdateController extends Controller
{
    protected GitHubService $gh;
    protected UpdateService $up;

    public function __construct(GitHubService $gh, UpdateService $up)
    {
        $this->middleware('auth');
        $this->gh = $gh;
        $this->up = $up;
    }

    public function listReleases()
    {
        $releases = $this->gh->fetchReleases();
        return response()->json(['success' => true, 'data' => $releases]);
    }

    public function downloadLatest(Request $request)
    {
        $this->authorizeAdmin();

        $options = $this->extractOptions($request);
        $res = $this->up->downloadAndQueueLatest($options);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'update_id' => $res['update_id'],
                'version' => $res['version'] ?? null,
                'options' => $res['options'] ?? $options,
            ]);
        }

        return response()->json(['success' => false, 'error' => $res['error']], 400);
    }

    public function status()
    {
        $this->authorizeAdmin();

        $last = SystemUpdate::orderBy('created_at', 'desc')->first();
        return response()->json(['success' => true, 'data' => $last]);
    }

    protected function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $role = strtolower((string) ($user->role ?? ''));
        if (! in_array($role, ['owner', 'admin', 'administrator', 'super_admin', 'superadmin'], true)) {
            abort(403, 'Forbidden.');
        }
    }

    protected function extractOptions(Request $request): array
    {
        $request->validate([
            'regenerate_app_key' => 'sometimes|boolean',
            'run_composer_install' => 'sometimes|boolean',
            'run_npm_install' => 'sometimes|boolean',
            'run_migrations' => 'sometimes|boolean',
        ]);

        return [
            'regenerate_app_key' => $request->boolean('regenerate_app_key'),
            'run_composer_install' => $request->boolean('run_composer_install', true),
            'run_npm_install' => $request->boolean('run_npm_install', true),
            'run_migrations' => $request->boolean('run_migrations', true),
        ];
    }
}
