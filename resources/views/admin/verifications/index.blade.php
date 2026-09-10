@extends('layouts.app')

@section('title', 'ID Verifications')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Admin Verification Portal</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['pending' => 'Pending ('.$counts['pending'].')', 'verified' => 'Verified ('.$counts['verified'].')', 'rejected' => 'Rejected ('.$counts['rejected'].')', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.verifications.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === $key ? 'bg-accent-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Submitted</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Resident</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">ID Type</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Status</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($verifications as $verification)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $verification->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $verification->resident?->full_name ?? 'Resident #'.$verification->resident_id }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ str_replace('_', ' ', ucwords($verification->id_type)) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300',
                                        'verified' => 'bg-green-600/10 text-green-600 dark:text-green-400',
                                        'rejected' => 'bg-red-600/10 text-red-600 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses[$verification->status] ?? '' }}">
                                    {{ ucfirst($verification->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.verifications.show', $verification->id) }}" class="btn-ghost text-sm px-3 py-1.5" wire:navigate>
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-600 dark:text-gray-400">
                                No ID verifications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $verifications->links() }}
    </div>
</div>
@endsection