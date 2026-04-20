@extends('layouts.central')

@section('title', 'Version Details')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="heading-font text-xl font-semibold">Version {{ $version->version }}</h2>
                    <p class="text-sm text-slate-500">{{ $version->release_name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.versions.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-slate-200">Back</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <strong>Type:</strong> {{ $version->type }}<br>
                    <strong>Status:</strong> {{ $version->status }}<br>
                    <strong>Release date:</strong> {{ $version->release_date ? $version->release_date->toDateString() : '—' }}
                </div>

                <div>
                    <strong>Download URL:</strong>
                    @if($version->download_url)
                        <a href="{{ $version->download_url }}" target="_blank" class="text-indigo-600">Download</a>
                    @else
                        —
                    @endif
                    <br>
                    <strong>Checksum:</strong> {{ $version->checksum ?? '—' }}
                </div>
            </div>

            <div class="mb-4">
                <h3 class="font-semibold mb-2">Description</h3>
                <div class="prose">{!! nl2br(e($version->description)) !!}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <h4 class="font-semibold">Features</h4>
                    @if(!empty($version->features))
                        <ul class="list-disc pl-5">
                            @foreach($version->features as $f)
                                <li>{{ $f }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div>—</div>
                    @endif
                </div>

                <div>
                    <h4 class="font-semibold">Fixes</h4>
                    @if(!empty($version->fixes))
                        <ul class="list-disc pl-5">
                            @foreach($version->fixes as $f)
                                <li>{{ $f }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div>—</div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="font-semibold mb-2">Update Logs</h3>
                <div class="mb-4">
                    <h4 class="font-semibold">Admin actions</h4>
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('admin.versions.download') }}">
                            @csrf
                            <input type="hidden" name="version" value="{{ $version->version }}">
                            <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded">Download package</button>
                        </form>

                        <form method="POST" action="{{ route('admin.versions.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="version" value="{{ $version->version }}">
                            <label class="block text-sm">Upload package (.zip)</label>
                            <input type="file" name="package" accept=".zip" class="mt-1" required>
                            <div class="mt-2"><button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Upload & Save</button></div>
                        </form>

                        <form method="POST" action="{{ route('admin.versions.install') }}">
                            @csrf
                            <input type="hidden" name="version" value="{{ $version->version }}">
                            <label class="block text-sm">File path (storage/app/...), leave blank to use download step output</label>
                            <div class="flex gap-2">
                                <input id="file_path_input" name="file_path" type="text" placeholder="updates/update-1.2.3.zip" class="form-control mt-1 w-full">
                                <select id="updates_select" class="form-select mt-1">
                                    <option value="">Pick file…</option>
                                </select>
                            </div>
                            <div class="mt-2"><button type="submit" class="px-3 py-2 bg-yellow-600 text-white rounded">Install package</button></div>
                        </form>

                        <form method="POST" action="{{ route('admin.versions.simulate') }}" target="_blank">
                            @csrf
                            <input type="hidden" name="version" value="{{ $version->version }}">
                            <div class="mt-2"><button type="submit" class="px-3 py-2 bg-rose-600 text-white rounded">Simulate local update (opens in new tab)</button></div>
                        </form>
                    </div>
                </div>
                @if($updateLogs->isEmpty())
                    <div>No update logs for this version.</div>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="py-2">Tenant</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Started</th>
                                <th class="py-2">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($updateLogs as $log)
                            <tr>
                                <td class="py-2">{{ $log->tenant->name ?? 'Central' }}</td>
                                <td class="py-2">{{ $log->status }}</td>
                                <td class="py-2">{{ $log->started_at ? $log->started_at->toDateTimeString() : '—' }}</td>
                                <td class="py-2">{{ $log->completed_at ? $log->completed_at->toDateTimeString() : '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
                    <script>
                        // Fetch list of local update files and populate dropdown
                        (function(){
                            fetch('{{ route('admin.versions.update-files') }}')
                                .then(r=>r.json())
                                .then(j=>{
                                    if(!j.success) return;
                                    const sel = document.getElementById('updates_select');
                                    j.files.forEach(f=>{
                                        const opt = document.createElement('option');
                                        opt.value = f.path;
                                        opt.text = f.name + ' (' + Math.round(f.size/1024) + ' KB)';
                                        sel.appendChild(opt);
                                    });
                                    sel.addEventListener('change', function(){
                                        const v = this.value;
                                        if(v) document.getElementById('file_path_input').value = v;
                                    });
                                })
                                .catch(()=>{});
                        })();
                    </script>
@endsection
