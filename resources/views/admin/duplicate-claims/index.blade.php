@extends('layouts.app')

@section('title', 'Duplicate Claims')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Duplicate Account Claims</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Date</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Resident Name</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Contact</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Matched Record</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Status</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $claim->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ strtoupper($claim->registration_data['last_name']) }}, {{ strtoupper($claim->registration_data['first_name']) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $claim->registration_data['contact_number'] ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($claim->matchedResident)
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $claim->matchedResident->full_name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-600 dark:text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300 dark:text-accent-300',
                                        'approved' => 'bg-green-600/10 text-green-600 dark:text-green-400',
                                        'dismissed' => 'bg-red-600/10 text-red-600 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses[$claim->status] ?? 'bg-gray-50 dark:bg-gray-950 text-gray-600 dark:text-gray-400' }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.duplicate-claims.show', $claim) }}" class="btn-ghost text-sm px-3 py-1.5" wire:navigate>
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-600 dark:text-gray-400">
                                No duplicate claims to review.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
