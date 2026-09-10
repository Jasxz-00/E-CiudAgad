@extends('layouts.app')

@section('title', 'ID Verification Review')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.verifications.index') }}" class="btn-ghost mb-4">&larr; Back</a>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-6">
            <div class="card">
                <h2 class="text-lg font-bold mb-3">Resident Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-600 dark:text-gray-400">Name</span><p class="font-medium">{{ $verification->resident?->full_name }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Birthdate</span><p class="font-medium">{{ $verification->resident?->birthdate?->format('M d, Y') }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Address</span><p class="font-medium">{{ $verification->resident?->full_address }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">Category</span><p class="font-medium">{{ $verification->resident?->category }}</p></div>
                </div>
            </div>

            <div class="card">
                <h2 class="text-lg font-bold mb-3">ID Submission</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-600 dark:text-gray-400">ID Type</span><p class="font-medium">{{ str_replace('_', ' ', ucwords($verification->id_type)) }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">ID Number</span><p class="font-medium">{{ $verification->id_number }}</p></div>
                    <div class="col-span-2"><span class="text-gray-600 dark:text-gray-400">Submitted</span><p class="font-medium">{{ $verification->created_at->format('M d, Y h:i A') }}</p></div>
                </div>

                @if($verification->rejected_reason)
                    <div class="mt-4 p-3 bg-red-600/10 rounded-xl text-sm text-red-600 dark:text-red-400">
                        <strong>Rejected:</strong> {{ $verification->rejected_reason }}
                    </div>
                @endif

                <div class="mt-6 space-y-4">
                    @php
                        $frontUrl = $verification->file_path ? route('storage.private', ['path' => $verification->file_path]) : null;
                        $backUrl = $verification->back_file_path ? route('storage.private', ['path' => $verification->back_file_path]) : null;
                    @endphp
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Front of ID</p>
                        @if($frontUrl)
                            <a href="{{ $frontUrl }}" target="_blank" class="btn-ghost text-sm">View Front</a>
                        @else
                            <p class="text-xs italic text-gray-500">File unavailable</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Back of ID</p>
                        @if($backUrl)
                            <a href="{{ $backUrl }}" target="_blank" class="btn-ghost text-sm">View Back</a>
                        @else
                            <p class="text-xs italic text-gray-500">File unavailable</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card h-fit" x-data="{ showRejectForm: false }">
            <h2 class="text-lg font-bold mb-3">Decision</h2>

            @if($verification->status === 'pending')
                <form method="POST" action="{{ route('admin.verifications.verify', $verification->id) }}" class="mb-4">
                    @csrf
                    <button type="submit" class="btn-primary w-full">Approve Verification</button>
                </form>

                <button type="button" @@click="showRejectForm = !showRejectForm" class="btn-danger w-full">{{ __('common.rejected') }}</button>

                <div x-show="showRejectForm" x-cloak class="mt-4 p-4 bg-red-600/10 rounded-xl">
                    <form method="POST" action="{{ route('admin.verifications.reject', $verification->id) }}">
                        @csrf
                        <label for="rejection_reason" class="label">Rejection Reason</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" class="input-field" required placeholder="e.g. ID is blurred / does not match the registrant"></textarea>
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="btn-danger text-sm">{{ __('common.confirm') }}</button>
                            <button type="button" @@click="showRejectForm = false" class="btn-ghost text-sm">{{ __('common.cancel') }}</button>
                        </div>
                    </form>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    This submission was {{ $verification->status }} on
                    {{ $verification->admin_reviewed_at?->format('M d, Y h:i A') }} by {{ $verification->verifiedBy?->username ?? '—' }}.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection