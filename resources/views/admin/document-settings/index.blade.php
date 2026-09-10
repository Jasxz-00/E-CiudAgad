@extends('layouts.app')

@section('title', 'Document Settings')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Document Settings</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.document-settings.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="chairman_name" class="label">Punong Barangay (Chairman) Name</label>
                    <input id="chairman_name" name="chairman_name" type="text" class="input-field" value="{{ old('chairman_name', $setting->chairman_name) }}" placeholder="e.g. HON. JUAN DELA CRUZ">
                    <p class="text-xs text-gray-500 mt-1">Printed on the signature line of every generated document.</p>
                </div>

                <div>
                    <label for="barangay_name" class="label">Barangay Name</label>
                    <input id="barangay_name" name="barangay_name" type="text" class="input-field" value="{{ old('barangay_name', $setting->barangay_name) }}" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="province_name" class="label">Province Name</label>
                        <input id="province_name" name="province_name" type="text" class="input-field" value="{{ old('province_name', $setting->province_name) }}" placeholder="e.g. CAVITE">
                    </div>
                    <div>
                        <label for="city_name" class="label">City/Municipality Name</label>
                        <input id="city_name" name="city_name" type="text" class="input-field" value="{{ old('city_name', $setting->city_name) }}" placeholder="e.g. BACOOR CITY">
                    </div>
                </div>

                <div>
                    <label for="barangay_address" class="label">Barangay Address</label>
                    <input id="barangay_address" name="barangay_address" type="text" class="input-field" value="{{ old('barangay_address', $setting->barangay_address) }}" placeholder="e.g. Bacoor City, Cavite">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="left_logo" class="label">Left Logo (top of document)</label>
                        @if($setting->left_logo_path && Storage::disk('private')->exists($setting->left_logo_path))
                            <img src="{{ route('storage.private', ['path' => $setting->left_logo_path]) }}" class="h-20 mb-2 object-contain" alt="Left logo">
                            <label class="flex items-center gap-2 text-sm mb-2">
                                <input type="checkbox" name="remove_left_logo" value="1" class="rounded">
                                Remove current logo
                            </label>
                        @endif
                        <input id="left_logo" name="left_logo" type="file" accept=".jpg,.jpeg,.png,.svg" class="input-field">
                    </div>
                    <div>
                        <label for="right_logo" class="label">Right Logo (top of document)</label>
                        @if($setting->right_logo_path && Storage::disk('private')->exists($setting->right_logo_path))
                            <img src="{{ route('storage.private', ['path' => $setting->right_logo_path]) }}" class="h-20 mb-2 object-contain" alt="Right logo">
                            <label class="flex items-center gap-2 text-sm mb-2">
                                <input type="checkbox" name="remove_right_logo" value="1" class="rounded">
                                Remove current logo
                            </label>
                        @endif
                        <input id="right_logo" name="right_logo" type="file" accept=".jpg,.jpeg,.png,.svg" class="input-field">
                    </div>
                </div>
                <p class="text-xs text-gray-500">Logos: JPG, PNG, or SVG, max 2MB. Recommended square format.</p>

                <div>
                    <label for="signature" class="label">Punong Barangay Signature</label>
                    @if($setting->signature_path && Storage::disk('private')->exists($setting->signature_path))
                        <img src="{{ route('storage.private', ['path' => $setting->signature_path]) }}" class="h-16 mb-2 object-contain" alt="Punong Barangay signature">
                        <label class="flex items-center gap-2 text-sm mb-2">
                            <input type="checkbox" name="remove_signature" value="1" class="rounded">
                            Remove current signature
                        </label>
                    @endif
                    <input id="signature" name="signature" type="file" accept=".jpg,.jpeg,.png,.svg" class="input-field">
                    <p class="text-xs text-gray-500 mt-1">Printed above the Punong Barangay signature line of every generated document. PNG with transparent background recommended, max 2MB.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card mt-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Barangay Officials</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Listed on the right side of every generated document (e.g. Punong Barangay, Kagawads, Secretary, Treasurer).</p>

        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 pr-4">Position</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Sort</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officials as $official)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 pr-4 font-medium">{{ $official->position_label }}</td>
                            <td class="py-2 pr-4">{{ $official->name }}</td>
                            <td class="py-2 pr-4 text-gray-500">{{ $official->sort_order }}</td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('admin.document-settings.officials.delete', $official->id) }}"
                                    onsubmit="return confirm('Remove {{ $official->name }} from the officials list?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-sm text-gray-500 dark:text-gray-400">No officials listed yet. Punong Barangay name from the settings above is used as fallback.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('admin.document-settings.officials.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <label for="position" class="label">Position</label>
                <select id="position" name="position" class="select-field" required>
                    <option value="punong_barangay">Punong Barangay</option>
                    <option value="kagawad">Kagawad</option>
                    <option value="secretary">Barangay Secretary</option>
                    <option value="treasurer">Barangay Treasurer</option>
                </select>
            </div>
            <div>
                <label for="official_name" class="label">Full Name</label>
                <input id="official_name" name="name" type="text" class="input-field uppercase-input" placeholder="e.g. HON. JUAN DELA CRUZ" required>
            </div>
            <div>
                <label for="sort_order" class="label">Sort Order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="255" class="input-field" value="0" placeholder="0">
            </div>
            <div class="sm:col-span-3">
                <button type="submit" class="btn-primary">Add Official</button>
            </div>
        </form>
    </div>

    <div class="mt-8 mb-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Document Layouts</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Edit the body text of each document. Changes are saved automatically per document. Leave a layout empty to use the default template.
        </p>
    </div>

    <div class="space-y-4">
        @foreach($documentTypes as $documentType)
            <div class="card" x-data="layoutEditor({{ $documentType->id }}, @js($documentType->layout_body ?? ''))">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $documentType->name }}</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $documentType->code }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium"
                              :class="status === 'saved' ? 'text-green-600 dark:text-green-400' : (status === 'saving' ? 'text-accent-600 dark:text-accent-300' : 'text-gray-500 dark:text-gray-400')">
                            <span x-text="status === 'saved' ? 'Saved ' + (savedAt || '') : (status === 'saving' ? 'Saving...' : '')"></span>
                        </span>
                        <button type="button" @@click="doReset()" class="btn-ghost text-xs px-3 py-1.5">Reset to default</button>
                    </div>
                </div>

                <textarea rows="6"
                    x-model="body"
                    @@input="queueSave()"
                    class="input-field font-mono text-sm w-full"
                    placeholder="Leave empty to use the default template body for this document."></textarea>

                <div class="mt-2" x-data="{ showTokens: false }">
                    <button type="button" @@click="showTokens = !showTokens" class="text-xs text-accent-700 dark:text-accent-300 hover:underline">
                        <span x-text="showTokens ? 'Hide' : 'Show'"></span> available placeholders
                    </button>
                    <div x-show="showTokens" x-cloak class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-1 bg-gray-50 dark:bg-gray-950 rounded-lg p-3">
                        @foreach($placeholders as $token => $desc)
                            <div class="text-xs">
                                <code class="text-accent-700 dark:text-accent-300">{{ $token }}</code>
                                @if(is_string($desc))
                                    <span class="text-gray-500 dark:text-gray-400">— {{ $desc }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    function layoutEditor(id, initialBody) {
        let timer = null;
        let syncing = false;

        return {
            body: initialBody,
            status: '',
            savedAt: '',
            queueSave() {
                this.status = 'saving';
                clearTimeout(timer);
                timer = setTimeout(() => this.save(), 1200);
            },
            save() {
                if (syncing) return;
                syncing = true;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch(@json(route('admin.document-settings.layouts.update', ['documentType' => '__ID__'])).replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ layout_body: this.body }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) { this.status = 'saved'; this.savedAt = data.saved_at; }
                        else { this.status = ''; }
                    })
                    .catch(() => { this.status = ''; })
                    .finally(() => { syncing = false; });
            },
            doReset() {
                if (!confirm('Restore the default template layout for this document? Your custom body will be removed.')) return;
                this.body = '';
                this.status = 'saving';
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch(@json(route('admin.document-settings.layouts.reset', ['documentType' => '__ID__'])).replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                }).then(() => { this.status = 'saved'; this.savedAt = ''; });
            },
        };
    }
</script>
@endpush