@extends('layouts.app')

@section('title', 'Review Duplicate Claim')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('admin.duplicate-claims.index') }}" wire:navigate class="btn-ghost p-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 min-w-0">Review Duplicate Claim</h1>
        <span class="ml-auto inline-block px-3 py-1 rounded-full text-xs font-medium
            @if($claim->status === 'pending') bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300 dark:text-accent-300
            @elseif($claim->status === 'approved') bg-green-600/10 text-green-600 dark:text-green-400
            @else bg-red-600/10 text-red-600 dark:text-red-400
            @endif">
            {{ ucfirst($claim->status) }}
        </span>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Claimant Data --}}
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Claimant Registration Data</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">First Name</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ strtoupper($claim->registration_data['first_name']) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Last Name</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ strtoupper($claim->registration_data['last_name']) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Middle Name</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ isset($claim->registration_data['middle_name']) ? strtoupper($claim->registration_data['middle_name']) : 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Birthdate</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $claim->registration_data['birthdate_year'] }}-{{ str_pad($claim->registration_data['birthdate_month'], 2, '0', STR_PAD_LEFT) }}-{{ str_pad($claim->registration_data['birthdate_day'], 2, '0', STR_PAD_LEFT) }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Gender</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($claim->registration_data['gender']) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Contact</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->registration_data['contact_number'] ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600 dark:text-gray-400">Email</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->registration_data['email'] ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Matched Resident Data --}}
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Matched Existing Record</h2>
            @if($claim->matchedResident)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Full Name</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->matchedResident->full_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Birthdate</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->matchedResident->birthdate->format('Y-m-d') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Gender</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($claim->matchedResident->gender) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Age</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->matchedResident->age }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Contact</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->matchedResident->contact_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Address</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $claim->matchedResident->full_address }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Category</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($claim->matchedResident->category) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600 dark:text-gray-400">Account Status</dt>
                        <dd class="font-medium">
                            @if($claim->matchedResident->user)
                                <span class="{{ $claim->matchedResident->user->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $claim->matchedResident->user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">No user account</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-gray-600 dark:text-gray-400 text-sm">No matched resident record found.</p>
            @endif
        </div>
    </div>

    {{-- Staff Notes & Actions --}}
    @if($claim->status === 'pending')
        <div class="card mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Staff Decision</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <form method="POST" action="{{ route('admin.duplicate-claims.approve', $claim) }}" class="space-y-3">
                    @csrf
                    <label for="approve_notes" class="label">Notes (Approval will create a new account)</label>
                    <textarea id="approve_notes" name="staff_notes" rows="3" class="input-field" placeholder="Optional notes about this approval..."></textarea>
                    <button type="submit" class="btn-primary w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Approve Registration (No Duplicate)
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.duplicate-claims.dismiss', $claim) }}" class="space-y-3">
                    @csrf
                    <label for="dismiss_notes" class="label">Notes (Dismissal confirms the duplicate)</label>
                    <textarea id="dismiss_notes" name="staff_notes" rows="3" class="input-field" placeholder="Explain why this is a confirmed duplicate..."></textarea>
                    <button type="submit" class="btn-danger w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Dismiss (Confirm Duplicate)
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="card mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Resolution Notes</h2>
            @if($claim->staff_notes)
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $claim->staff_notes }}</p>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400">No notes provided.</p>
            @endif
            @if($claim->reviewer)
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Reviewed by {{ $claim->reviewer->email }} on {{ $claim->reviewed_at->format('M d, Y h:i A') }}</p>
            @endif
        </div>
    @endif
</div>
@endsection
