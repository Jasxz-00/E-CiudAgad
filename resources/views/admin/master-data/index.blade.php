@extends('layouts.app')

@section('title', 'Master Data')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Master Data Management</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Document Types --}}
        <div class="card">
            <h2 class="text-lg font-semibold mb-4">Document Types</h2>
            <div class="mb-4">
                <form method="POST" action="{{ route('admin.document-types.store', ['type' => 'document-types']) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="name" placeholder="Name" class="input-field text-sm" required>
                    <input type="text" name="code" placeholder="Code (e.g., BRGY_CLEARANCE)" class="input-field text-sm uppercase-input" required>
                    <select name="complexity" class="select-field text-sm" required>
                        <option value="simple">Simple</option><option value="moderate">Moderate</option><option value="complex">Complex</option>
                    </select>
                    <div class="flex gap-2">
                        <input type="number" name="complexity_weight" placeholder="Weight" step="0.01" class="input-field text-sm w-1/2" required>
                        <input type="number" name="processing_fee" placeholder="Fee" step="0.01" class="input-field text-sm w-1/2" required>
                    </div>
                    <button type="submit" class="btn-primary text-sm w-full">Add Document Type</button>
                </form>
            </div>
            <div class="max-h-64 overflow-y-auto space-y-2">
                @foreach($documentTypes as $dt)
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-950 rounded-lg text-sm">
                    <span>{{ $dt->name }} <span class="text-xs text-gray-600 dark:text-gray-400">({{ $dt->complexity }}, {{ $dt->complexity_weight }})</span></span>
                    <span class="text-xs {{ $dt->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $dt->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Purposes --}}
        <div class="card">
            <h2 class="text-lg font-semibold mb-4">Request Purposes</h2>
            <div class="mb-4">
                <form method="POST" action="{{ route('admin.purposes.store', ['type' => 'purposes']) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="name" placeholder="Name" class="input-field text-sm" required>
                    <input type="text" name="code" placeholder="Code (e.g., MEDICAL)" class="input-field text-sm uppercase-input" required>
                    <input type="number" name="priority_weight" placeholder="Weight" step="0.01" class="input-field text-sm" required>
                    <button type="submit" class="btn-primary text-sm w-full">Add Purpose</button>
                </form>
            </div>
            <div class="max-h-64 overflow-y-auto space-y-2">
                @foreach($purposes as $p)
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-950 rounded-lg text-sm">
                    <span>{{ $p->name }} <span class="text-xs text-gray-600 dark:text-gray-400">({{ $p->priority_weight }})</span></span>
                    <span class="text-xs {{ $p->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Categories --}}
        <div class="card">
            <h2 class="text-lg font-semibold mb-4">Resident Categories</h2>
            <div class="mb-4">
                <form method="POST" action="{{ route('admin.categories.store', ['type' => 'categories']) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="name" placeholder="Name (e.g., regular)" class="input-field text-sm" required>
                    <input type="text" name="display_name" placeholder="Display Name (e.g., Regular)" class="input-field text-sm" required>
                    <input type="number" name="weight" placeholder="Weight" step="0.01" class="input-field text-sm" required>
                    <button type="submit" class="btn-primary text-sm w-full">Add Category</button>
                </form>
            </div>
            <div class="max-h-64 overflow-y-auto space-y-2">
                @foreach($categories as $c)
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-950 rounded-lg text-sm">
                    <span>{{ $c->display_name }} <span class="text-xs text-gray-600 dark:text-gray-400">({{ $c->weight }})</span></span>
                    <span class="text-xs {{ $c->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
