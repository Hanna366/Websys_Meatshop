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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Version</label>
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

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary-gradient px-4 py-2 rounded-md">Create</button>
                    <a href="{{ route('admin.versions.index') }}" class="px-4 py-2 rounded-md border border-slate-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
