@extends('layouts.app')

@section('content')
<div class="container">
    <h1>System Update</h1>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Current version:</strong> {{ $current }}</p>
            @if($latest)
                <p><strong>Latest release:</strong> {{ $latest['tag_name'] ?? 'unknown' }} @if(($latest['tag_name'] ?? null) !== $current) <span class="badge bg-warning">Update Available</span> @endif</p>
                <p>{{ $latest['name'] ?? '' }}</p>
            @else
                <p>Latest release: <em>unknown</em></p>
            @endif

            <form method="POST" action="{{ route('admin.update.perform') }}">
                @csrf
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="run_composer_install" name="run_composer_install" value="1" checked>
                        <label class="form-check-label" for="run_composer_install">Run Composer install</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="run_npm_install" name="run_npm_install" value="1" checked>
                        <label class="form-check-label" for="run_npm_install">Run NPM install if a <code>package.json</code> exists</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="run_migrations" name="run_migrations" value="1" checked>
                        <label class="form-check-label" for="run_migrations">Run database migrations</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="regenerate_app_key" name="regenerate_app_key" value="1">
                        <label class="form-check-label" for="regenerate_app_key">Generate a new app key</label>
                    </div>
                    <small class="text-muted d-block mt-2">Leave app key generation off unless you intentionally want to rotate encryption keys and invalidate existing encrypted sessions/data.</small>
                </div>
                <button class="btn btn-primary">Update System</button>
            </form>

            <div class="mt-3">
                <strong>Status:</strong>
                <span id="update-status">{{ $status->status ?? 'idle' }}</span>
                @if($status?->version)
                    <span class="ms-2 text-muted">({{ $status->version }})</span>
                @endif
            </div>
            @if($status?->notes)
                <div class="mt-2 text-muted">{{ $status->notes }}</div>
            @endif
        </div>
    </div>
</div>

<script>
    async function pollStatus(){
        try{
            const res = await fetch('{{ route('admin.update.status') }}', {credentials: 'same-origin'});
            const json = await res.json();
            document.getElementById('update-status').innerText = json.status || json.data?.status || 'idle';
        }catch(e){
            console.error(e);
        }
    }

    setInterval(pollStatus, 5000);
</script>
@endsection
