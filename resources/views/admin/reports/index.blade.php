@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Reports & Analytics</h1>

    <div class="card mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div>
                <label for="date_from" class="label">Date From</label>
                <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" class="input-field" autocomplete="off">
            </div>
            <div>
                <label for="date_to" class="label">Date To</label>
                <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" class="input-field" autocomplete="off">
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn-accent">Export CSV</a>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Total</p><p class="text-xl font-bold">{{ $summary['total'] }}</p></div>
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Pending</p><p class="text-xl font-bold text-accent-700 dark:text-accent-300 dark:text-accent-400">{{ $summary['pending'] }}</p></div>
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Approved</p><p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $summary['approved'] }}</p></div>
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Completed</p><p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $summary['completed'] }}</p></div>
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Rejected</p><p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $summary['rejected'] }}</p></div>
        <div class="card p-4 text-center"><p class="text-xs text-gray-600 dark:text-gray-400">Released</p><p class="text-xl font-bold text-primary-700 dark:text-primary-400">{{ $summary['released'] }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="card">
            <h3 class="font-semibold mb-3">By Document Type</h3>
            <div class="space-y-2">
                @foreach($byDocumentType as $name => $count)
                <div class="flex justify-between text-sm"><span>{{ $name }}</span><span class="font-mono">{{ $count }}</span></div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <h3 class="font-semibold mb-3">By Purpose</h3>
            <div class="space-y-2">
                @foreach($byPurpose as $name => $count)
                <div class="flex justify-between text-sm"><span>{{ $name }}</span><span class="font-mono">{{ $count }}</span></div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <h3 class="font-semibold mb-3">By Category</h3>
            <div class="space-y-2">
                @foreach($byCategory as $name => $count)
                <div class="flex justify-between text-sm"><span class="capitalize">{{ $name }}</span><span class="font-mono">{{ $count }}</span></div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="font-semibold mb-4">Request List</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700"><th class="pb-2 font-medium">#</th><th class="pb-2 font-medium">Resident</th><th class="pb-2 font-medium">Document</th><th class="pb-2 font-medium">Purpose</th><th class="pb-2 font-medium">Status</th><th class="pb-2 font-medium">Date</th></tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 font-mono">{{ $req->queue_number }}</td>
                        <td class="py-2">{{ $req->resident?->full_name ?? 'N/A' }}</td>
                        <td class="py-2">{{ $req->documentType?->name }}</td>
                        <td class="py-2">{{ $req->purpose?->name }}</td>
                        <td class="py-2"><span class="badge-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="py-2">{{ $req->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
