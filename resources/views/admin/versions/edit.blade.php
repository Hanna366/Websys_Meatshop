@extends('layouts.central')

@section('title', 'Edit Version')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-card p-6">
            <h2 class="heading-font text-xl font-semibold mb-4">Edit Version {{ $version->version }}</h2>

            <form action="{{ route('admin.versions.update', ['version' => $version->getKey() ?? request()->route('version')]) }}" method="post">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium">Release name</label>
                    <input type="text" name="release_name" value="{{ old('release_name', $version->release_name) }}" class="w-full mt-1 p-2 border rounded" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" rows="5" class="w-full mt-1 p-2 border rounded">{{ old('description', $version->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium">Status</label>
                        <select name="status" class="w-full mt-1 p-2 border rounded">
                            @foreach(['development','testing','stable','deprecated'] as $s)
                                <option value="{{ $s }}" {{ old('status', $version->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Release date</label>
                        <input type="date" name="release_date" value="{{ old('release_date', $version->release_date ? $version->release_date->format('Y-m-d') : '') }}" class="w-full mt-1 p-2 border rounded" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Checksum</label>
                        <input type="text" name="checksum" value="{{ old('checksum', $version->checksum) }}" class="w-full mt-1 p-2 border rounded" />
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Features (one per line)</label>
                    <textarea name="features_text" rows="4" class="w-full mt-1 p-2 border rounded">{{ old('features_text', is_array($version->features) ? implode("\n", $version->features) : '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Fixes (one per line)</label>
                    <textarea name="fixes_text" rows="4" class="w-full mt-1 p-2 border rounded">{{ old('fixes_text', is_array($version->fixes) ? implode("\n", $version->fixes) : '') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
                    <a href="{{ route('admin.versions.show', ['version' => $version->getKey() ?? request()->route('version')]) }}" class="px-4 py-2 border rounded">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Convert textarea multi-line to array inputs before submit
        document.querySelector('form').addEventListener('submit', function(e){
            const form = this;
            ['features_text','fixes_text'].forEach(function(name){
                const ta = form.querySelector('[name="'+name+'"]');
                if (!ta) return;
                const lines = ta.value.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
                // remove any existing inputs for this name
                form.querySelectorAll('input[name="'+name.replace('_text','')+'[]"]').forEach(n=>n.remove());
                lines.forEach(function(l){
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = name.replace('_text','') + '[]';
                    inp.value = l;
                    form.appendChild(inp);
                });
            });
        });
    </script>
@endsection
