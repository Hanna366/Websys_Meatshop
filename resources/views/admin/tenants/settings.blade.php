@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Settings for {{ $tenant->business_name }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.tenants.settings.update', $tenant->id) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Theme</label>
            <input type="text" name="theme" class="form-control" value="{{ old('theme', $setting->theme) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Primary Color</label>
            <input type="text" name="primary_color" class="form-control" value="{{ old('primary_color', $setting->primary_color) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Meta (JSON keys)</label>
            <textarea name="meta" class="form-control" rows="4">{{ old('meta', json_encode($setting->meta ?? [])) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Logo</label>
            @if($setting->logo_path)
                <div><img src="{{ asset('storage/' . $setting->logo_path) }}" alt="logo" style="max-height:80px"></div>
            @endif
            <input type="file" name="logo" class="form-control">
        </div>

        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
