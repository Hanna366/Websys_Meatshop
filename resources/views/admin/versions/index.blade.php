@extends('layouts.central')

@section('title', 'Version Management - MeatShop Central')

@section('content')
<div class="space-y-6">
    <!-- Version Overview Cards -->
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Latest Version</p>
                    <h3 class="heading-font text-2xl font-semibold {{ $updateInfo['update_available'] ? 'text-emerald' : 'text-slate' }}-700">{{ $updateInfo['latest_version'] }}</h3>
                    <p class="mt-1 text-xs text-{{ $updateInfo['update_available'] ? 'emerald' : 'slate' }}-600">
                        {{ $updateInfo['update_available'] ? 'Update Available' : 'Up to Date' }}
                    </p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $updateInfo['update_available'] ? 'emerald' : 'slate' }}-50 text-{{ $updateInfo['update_available'] ? 'emerald' : 'slate' }}-700">
                    <i data-lucide="{{ $updateInfo['update_available'] ? 'download' : 'check-circle' }}" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-{{ $updateInfo['update_available'] ? 'emerald' : 'slate' }}-500/60 to-{{ $updateInfo['update_available'] ? 'emerald' : 'slate' }}-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Total Versions</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-700">{{ $versions->count() }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ $versions->where('status', 'stable')->count() }} stable</p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-700">
                    <i data-lucide="layers" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-slate-500/60 to-slate-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Update History</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-700">{{ count($updateHistory) }}</h3>
                    <p class="mt-1 text-xs text-slate-500">total updates</p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-700">
                    <i data-lucide="history" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-slate-500/60 to-slate-100"></div>
        </article>
    </section>

    <!-- Update Alert -->
    @if($updateInfo['update_available'])
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i data-lucide="download" class="h-5 w-5 text-emerald-600"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-emerald-800">Update Available</h3>
                <p class="mt-1 text-sm text-emerald-700">
                    Version {{ $updateInfo['latest_version'] }} is now available! You're currently running {{ $currentVersion }}.
                </p>
                <div class="mt-2 flex gap-2">
                    <button onclick="downloadUpdate('{{ $updateInfo['latest_version'] }}')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                        <i data-lucide="download" class="h-3 w-3"></i>
                        Download Update
                    </button>
                    @if(!empty($updateInfo['update_info']['id']))
                        <a href="{{ route('admin.versions.show', $updateInfo['update_info']['id']) }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200">
                            <i data-lucide="info" class="h-3 w-3"></i>
                            View Details
                        </a>
                    @elseif(!empty($updateInfo['update_info']['release_url']))
                        <a href="{{ $updateInfo['update_info']['release_url'] }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200">
                            <i data-lucide="info" class="h-3 w-3"></i>
                            View Release
                        </a>
                    @else
                        <button disabled class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-400" title="Details not available">
                            <i data-lucide="info" class="h-3 w-3"></i>
                            View Details
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- GitHub Integration Section -->
    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-0 text-lg font-semibold text-slate-900">GitHub Integration</h2>
                <p class="mb-0 text-sm text-slate-500">Sync releases from your GitHub repository.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 text-sm">
                    <i data-lucide="github" class="h-4 w-4 text-slate-600"></i>
                    <span class="text-slate-600">{{ env('GITHUB_REPO_OWNER', 'Hanna366') }}/{{ env('GITHUB_REPO_NAME', 'Websys_Meatshop') }}</span>
                </div>
                <button onclick="syncGitHub(event)" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    Sync GitHub
                </button>
                <button onclick="clearGitHubCache()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    Clear Cache
                </button>
            </div>
        </div>
        <div class="px-5 py-4">
            @if(!empty($githubReleases))
                <div class="space-y-3">
                    @foreach(array_slice($githubReleases, 0, 3) as $release)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-700">
                                    <i data-lucide="tag" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">
                                        {{ $release['tag_name'] }}
                                        @if($release['is_latest'])
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">Latest</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $release['name'] }} · {{ \Carbon\Carbon::parse($release['published_at'])->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500">{{ $release['download_count'] }} downloads</p>
                                <a href="{{ $release['html_url'] }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-700">View on GitHub</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(count($githubReleases) > 3)
                    <div class="mt-3 text-center">
                        <button onclick="viewAllGitHubReleases()" class="text-sm text-indigo-600 hover:text-indigo-700">View all {{ count($githubReleases) }} releases</button>
                    </div>
                @endif
            @else
                <div class="text-center py-6">
                    <i data-lucide="github" class="h-12 w-12 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-sm text-slate-500">No GitHub releases found</p>
                    <p class="text-xs text-slate-400 mt-1">Make sure your repository has releases tagged</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Versions Table -->
    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-0 text-lg font-semibold text-slate-900">Version History</h2>
                <p class="mb-0 text-sm text-slate-500">Manage application versions and updates.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.versions.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-sm font-medium text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-600 hover:text-white">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Create Version
                </a>
                <button onclick="checkUpdates()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    Check Updates
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Version</th>
                        <th class="px-5 py-3.5 font-semibold">Release Name</th>
                        <th class="px-5 py-3.5 font-semibold">Type</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold">Release Date</th>
                        <th class="px-5 py-3.5 font-semibold">Features</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($versions as $version)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-900">{{ $version->version }}</span>
                                    @if($version->version === $currentVersion)
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700">Current</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">{{ $version->release_name ?? 'N/A' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-{{ $version->type_color }}-50 px-2 py-1 text-xs font-medium text-{{ $version->type_color }}-700">
                                    {{ ucfirst($version->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-{{ $version->status_color }}-50 px-2 py-1 text-xs font-medium text-{{ $version->status_color }}-700">
                                    {{ ucfirst($version->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $version->release_date ? $version->release_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($version->features && count($version->features) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($version->features, 0, 2) as $feature)
                                            <span class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-xs text-slate-700">
                                                {{ Str::limit($feature, 20) }}
                                            </span>
                                        @endforeach
                                        @if(count($version->features) > 2)
                                            <span class="text-xs text-slate-500">+{{ count($version->features) - 2 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">No features</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.versions.show', $version) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                        <i data-lucide="eye" class="h-3 w-3"></i>
                                        View
                                    </a>
                                    <a href="{{ route('admin.versions.edit', $version) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                        <i data-lucide="edit" class="h-3 w-3"></i>
                                        Edit
                                    </a>
                                    @if($version->download_url && $version->version !== $currentVersion)
                                        <button onclick="downloadUpdate('{{ $version->version }}')" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                                            <i data-lucide="download" class="h-3 w-3"></i>
                                            Download
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i data-lucide="package" class="h-12 w-12 text-slate-300"></i>
                                    <span class="text-sm font-medium">No versions created yet</span>
                                    <span class="text-xs text-slate-400">Create your first version to start managing updates</span>
                                    <a href="{{ route('admin.versions.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50">
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                        Create Version
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Recent Update Activity -->
    @if(!empty($updateHistory))
    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="border-b border-slate-200/70 px-5 py-4">
            <h2 class="heading-font mb-0 text-lg font-semibold text-slate-900">Recent Update Activity</h2>
            <p class="mb-0 text-sm text-slate-500">Latest update attempts across all tenants.</p>
        </div>
        <div class="px-5 py-4">
            <div class="space-y-3">
                @foreach(array_slice($updateHistory, 0, 5) as $log)
                    @php
                        $statusColor = $log['status_color'] ?? 'slate';
                        $statusIcon = $log['status_icon'] ?? 'refresh-cw';
                        $statusLabel = ucfirst($log['status'] ?? 'unknown');
                    @endphp
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700">
                                <i data-lucide="{{ $statusIcon }}" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $log['from_version'] }} {{ $log['status'] === 'completed' ? 'to' : 'to' }} {{ $log['to_version'] }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $log['started_at'] ? \Carbon\Carbon::parse($log['started_at'])->format('M d, Y H:i') : 'N/A' }}
                                    @if($log['duration'])
                                        · {{ $log['duration'] }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-{{ $statusColor }}-50 px-2 py-1 text-xs font-medium text-{{ $statusColor }}-700">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(count($updateHistory) > 5)
                <div class="mt-3 text-center">
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700">View all update history</a>
                </div>
            @endif
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
function checkUpdates() {
    fetch('{{ route("admin.versions.check-updates") }}')
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Update check failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error checking updates:', error);
            alert('Update check failed: ' + error.message);
        });
}

function downloadUpdate(version) {
    if (confirm('Are you sure you want to download version ' + version + '?')) {
        fetch('{{ route("admin.versions.download") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                version: version
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Update downloaded successfully! Source: ' + (data.source || 'local'));
                // You can add installation logic here
            } else {
                alert('Error downloading update: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error downloading update:', error);
        });
    }
}

function syncGitHub(e) {
    const button = e && e.currentTarget ? e.currentTarget : (e && e.target) || document.querySelector('button[onclick^="syncGitHub"]');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i data-lucide="refresh-cw" class="h-4 w-4 animate-spin"></i> Syncing...';
    }

    fetch('{{ route("admin.versions.sync-github") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(payload => {
        // payload may be { success: true, message: '', data: { success: false, errors: [...], total_releases: 0 } }
        const result = payload && payload.data ? payload.data : payload;

        if (result && result.success === false) {
            // Detailed failure reported by backend
            alert('GitHub sync completed with errors: ' + (payload.message || (result.errors || []).join('; ') || 'Unknown error'));
            return;
        }

        if (result && typeof result.total_releases !== 'undefined' && result.total_releases === 0) {
            alert(payload.message || 'No releases found on GitHub');
            // still reload to show empty state
            location.reload();
            return;
        }

        // Otherwise treat as success
        alert(payload.message || 'GitHub sync completed successfully!');
        location.reload();
    })
    .catch(error => {
        console.error('Error syncing GitHub:', error);
        alert('Error syncing GitHub releases');
    })
    .finally(() => {
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i data-lucide="refresh-cw" class="h-4 w-4"></i> Sync GitHub';
        }
    });
}

function clearGitHubCache() {
    if (confirm('Are you sure you want to clear the GitHub cache?')) {
        fetch('{{ route("admin.versions.clear-github-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('GitHub cache cleared successfully!');
                location.reload();
            } else {
                alert('Failed to clear GitHub cache');
            }
        })
        .catch(error => {
            console.error('Error clearing GitHub cache:', error);
            alert('Error clearing GitHub cache');
        });
    }
}

function viewAllGitHubReleases() {
    window.open('https://github.com/{{ env("GITHUB_REPO_OWNER", "Hanna366") }}/{{ env("GITHUB_REPO_NAME", "Websys_Meatshop") }}/releases', '_blank');
}
</script>
@endpush

