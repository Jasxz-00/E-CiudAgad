@extends('layouts.app')

@section('title', 'WFQ Configuration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        showAdd: false,
        showEdit: false,
        editId: null,
        editLabel: '',
        editWeight: '',
        editActive: true,
        openAdd() { this.showAdd = true; },
        closeAdd() { this.showAdd = false; },
        openEdit(id, label, weight, active) {
            this.editId = id;
            this.editLabel = label;
            this.editWeight = weight;
            this.editActive = active;
            this.showEdit = true;
        },
        closeEdit() { this.showEdit = false; }
     }">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 min-w-0">WFQ Configuration</h1>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @@click="openAdd()" class="btn-primary text-sm">
                <svg class="w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Configuration
            </button>
            <form method="POST" action="{{ route('admin.wfq.recalculate') }}" class="inline">
                @csrf
                <button type="submit" class="btn-accent text-sm" onclick="return confirm('Recalculate queue? This will reorder all pending requests.')">Recalculate Queue</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $titles = [
            'category_weight' => 'Resident Category Weights',
            'complexity_weight' => 'Document Complexity Weights',
            'purpose_weight' => 'Purpose Priority Weights',
        ];
    @endphp

    @forelse($grouped as $type => $configs)
    <div class="card mb-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-lg font-semibold">{{ $titles[$type] ?? ucfirst($type) }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b">
                        <th class="pb-3 font-medium">Key</th>
                        <th class="pb-3 font-medium">Label</th>
                        <th class="pb-3 font-medium">Weight</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3 font-mono text-xs">{{ $config->config_key }}</td>
                        <td class="py-3">{{ $config->config_value }}</td>
                        <td class="py-3 font-mono">{{ number_format((float) $config->weight, 4) }}</td>
                        <td class="py-3">{!! $config->is_active ? '<span class="text-green-600 dark:text-green-400 font-medium">Active</span>' : '<span class="text-red-600 dark:text-red-400 font-medium">Inactive</span>' !!}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" @@click="openEdit({{ $config->id }}, @json($config->config_value), {{ $config->weight }}, {{ $config->is_active ? 'true' : 'false' }})"
                                    class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">Edit</button>
                                <form method="POST" action="{{ route('admin.wfq.destroy', $config->id) }}"
                                    onsubmit="return confirm('Delete this configuration entry? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm inline-flex items-center min-h-[36px]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <p class="text-sm text-gray-600 dark:text-gray-400">No WFQ configurations yet. Click "Add Configuration" to create one.</p>
    </div>
    @endforelse
</div>

{{-- Add Modal --}}
<div x-cloak x-show="showAdd" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">Add Configuration</h3>
        <form method="POST" action="{{ route('admin.wfq.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="config_type" class="label">Config Type</label>
                <select id="config_type" name="config_type" class="select-field" required autocomplete="off">
                    <option value="">Select type</option>
                    @foreach($allowedTypes as $type)
                        <option value="{{ $type }}" {{ old('config_type') == $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
                @error('config_type') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="config_key" class="label">Key</label>
                <input type="text" name="config_key" id="config_key" value="{{ old('config_key') }}"
                    class="input-field" placeholder="e.g., simple (category/complexity) or MEDICAL (purpose)" required autocomplete="off">
                @error('config_key') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="config_value" class="label">Label</label>
                <input type="text" name="config_value" id="config_value" value="{{ old('config_value') }}"
                    class="input-field" placeholder="e.g., Simple" required autocomplete="off">
                @error('config_value') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="weight" class="label">Weight Value</label>
                <input type="number" name="weight" id="weight" step="0.0001" min="0.0001" max="99.9999"
                    value="{{ old('weight') }}" class="input-field" required autocomplete="off">
                @error('weight') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                    <span class="text-sm">Active</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" @@click="closeAdd()" class="btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div x-cloak x-show="showEdit" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Edit Configuration</h3>
        <form method="POST" action="{{ route('admin.wfq.update', ':id') }}" class="space-y-4" x-bind:action="`{{ route('admin.wfq.update', ':id') }}`.replace(':id', editId)">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-600 dark:text-gray-400">Editing: <span class="font-medium text-gray-900 dark:text-gray-100" x-text="editLabel"></span></p>
            <div>
                <label for="edit-config-value" class="label">Label</label>
                <input type="text" name="config_value" id="edit-config-value" x-model="editLabel" class="input-field" required autocomplete="off">
            </div>
            <div>
                <label for="edit-weight" class="label">Weight Value</label>
                <input type="number" name="weight" id="edit-weight" step="0.0001" min="0.0001" max="99.9999" x-model="editWeight" class="input-field" required autocomplete="off">
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit-is-active" value="1" x-model="editActive" class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                    <span class="text-sm">Active</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" @@click="closeEdit()" class="btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection