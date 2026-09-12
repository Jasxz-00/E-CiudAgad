@extends('layouts.app')

@section('title', 'Master Data')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Master Data Management</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Document Types (no weights: document type does not affect queue priority) --}}
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Document Types</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Code</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentTypes as $dt)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3">{{ $dt->name }}</td>
                        <td class="py-3 font-mono">{{ $dt->code }}</td>
                        <td class="py-3">
                            <span class="text-xs {{ $dt->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $dt->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Purposes --}}
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Request Purposes</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Code</th>
                        <th class="pb-3 font-medium">Weight</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purposes as $p)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3">{{ $p->name }}</td>
                        <td class="py-3 font-mono">{{ $p->code }}</td>
                        <td class="py-3"><span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs font-mono">{{ number_format((float) $p->priority_weight, 2) }}</span></td>
                        <td class="py-3">
                            <span class="text-xs {{ $p->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Categories --}}
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Resident Categories</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-3 font-medium">Display Name</th>
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Weight</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $c)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-3">{{ $c->display_name }}</td>
                        <td class="py-3">{{ $c->name }}</td>
                        <td class="py-3"><span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-900 rounded text-xs font-mono">{{ number_format((float) $c->weight, 2) }}</span></td>
                        <td class="py-3">
                            <span class="text-xs {{ $c->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection