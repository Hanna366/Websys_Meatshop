@extends('layouts.central')

@section('title', 'Create Version')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="heading-font text-xl font-semibold">Create New Version</h2>
                    <p class="text-sm text-slate-500">Add a release entry for system versions</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.versions.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-slate-200">Back</a>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.versions.store') }}" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                    For the GitHub latest-release updater, the main fields you usually fill in here are:
                    <strong>version</strong>, <strong>release name</strong>, <strong>description</strong>, <strong>type</strong>, <strong>status</strong>, and <strong>release date</strong>.
                    Leave <strong>download URL</strong> blank unless you also host a direct ZIP package for manual downloads.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Version</label>
                        <p class="mb-1 text-xs text-slate-500">Use the release tag without the leading <code>v</code>, for example <code>1.2.3</code>.</p>
                        <input name="version" type="text" maxlength="20" required class="form-control form-input w-full" value="{{ old('version') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Release name</label>
                        <input name="release_name" type="text" maxlength="255" class="form-control form-input w-full" value="{{ old('release_name') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="form-control form-input w-full">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" required class="form-select w-full">
                            <option value="major" {{ old('type')=='major' ? 'selected' : '' }}>major</option>
                            <option value="minor" {{ old('type')=='minor' ? 'selected' : '' }}>minor</option>
                            <option value="patch" {{ old('type')=='patch' ? 'selected' : '' }}>patch</option>
                            <option value="hotfix" {{ old('type')=='hotfix' ? 'selected' : '' }}>hotfix</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" required class="form-select w-full">
                            <option value="development" {{ old('status')=='development' ? 'selected' : '' }}>development</option>
                            <option value="testing" {{ old('status')=='testing' ? 'selected' : '' }}>testing</option>
                            <option value="stable" {{ old('status')=='stable' ? 'selected' : '' }}>stable</option>
                            <option value="deprecated" {{ old('status')=='deprecated' ? 'selected' : '' }}>deprecated</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Release date</label>
                        <input name="release_date" type="date" class="form-control form-input w-full" value="{{ old('release_date') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Download URL</label>
                    <input name="download_url" type="url" class="form-control form-input w-full" value="{{ old('download_url') }}" placeholder="https://github.com/owner/repo/releases/download/v1.2.3/meatshop.zip">
                    <p class="mt-1 text-xs text-slate-500">Optional. Only fill this when you want this version row to point to a direct downloadable ZIP.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input name="is_mandatory" type="checkbox" value="1" class="mt-1" {{ old('is_mandatory') ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-slate-700">Mandatory update</span>
                            <span class="block text-xs text-slate-500">Use this when tenants should be pushed to install this release.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                        <input name="auto_update" type="checkbox" value="1" class="mt-1" {{ old('auto_update') ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-slate-700">Eligible for auto update</span>
                            <span class="block text-xs text-slate-500">Mark this if this release is safe to install through the updater flow.</span>
                        </span>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary-gradient px-4 py-2 rounded-md">Create</button>
                    <a href="{{ route('admin.versions.index') }}" class="px-4 py-2 rounded-md border border-slate-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
