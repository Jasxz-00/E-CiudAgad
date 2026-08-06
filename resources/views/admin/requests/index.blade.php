@extends('layouts.app')

@section('title', 'Request Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Request Management</h1>

    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 sm:gap-4">
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
            </div>
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
            </div>
            <div class="sm:col-span-2 md:col-span-2 lg:col-span-2">
                <label class="text-xs text-gray-600 dark:text-gray-400">Search</label>
                <input type="text" name="search" placeholder="Queue number or resident..." value="{{ request('search') }}" class="input-field">
            </div>
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Status</label>
                <select name="status" class="select-field">
                    <option value="">All</option>
                    @foreach(['pending', 'reviewing', 'approved', 'rejected', 'completed', 'released'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400">Category</label>
                <select name="category" class="select-field">
                    <option value="">All</option>
                    @foreach(['regular', 'senior', 'pwd', 'pregnant'] as $c)
                        <option value="{{ $c }}" {{ request('category') == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">Filter</button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table :headers="['Date Issued', 'Resident', 'Document', 'Purpose', 'Category', 'Queue #', 'Weight', 'Status', 'Actions']">
            @forelse($requests as $req)
            <tr>
                <td class="text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y') }}</td>
                <td>{{ $req->resident?->full_name ?? 'N/A' }}</td>
                <td>{{ $req->documentType?->name }}</td>
                <td>{{ $req->purpose?->name ?? $req->purpose_other }}</td>
                <td class="capitalize">{{ $req->resident?->category ?? '-' }}</td>
                <td class="font-mono">{{ $req->queue_number }}</td>
                <td>{{ number_format((float) $req->total_weight, 2) }}</td>
                <td><x-badge :status="$req->status" /></td>
                <td><a href="{{ route('admin.requests.show', $req->id) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">View</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-8 text-gray-600 dark:text-gray-400">No requests found.</td>
            </tr>
            @endforelse
        </x-table>
        @if($requests->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $requests->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
