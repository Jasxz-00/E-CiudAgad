@extends('layouts.app')

@section('title', 'New Document Request')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-6">
        <button type="button" onclick="history.back()" class="btn-ghost p-2" aria-label="Go back">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">New Document Request</h1>
    </div>

    <div class="card">
        <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-xl text-sm text-primary-700 dark:text-primary-400">
            <p class="font-medium">Hi, {{ $resident->first_name }}!</p>
            <p>Your personal information has been pre-filled. Just select the document and purpose below.</p>
        </div>

        <div class="mb-6 p-4 {{ $limitReached ? 'bg-red-600/10 border border-danger/30 text-red-600 dark:text-red-400' : 'bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400' }} rounded-xl text-sm">
            <p>
                You currently have <strong>{{ $activeCount }}</strong> of <strong>{{ $maxActiveRequests }}</strong> active request(s) (pending/reviewing).
                You may have up to 2 active requests, each for a <strong>different document type</strong>.
            </p>
            @if($limitReached)
                <p class="mt-1 font-medium">You have reached the maximum number of active requests. Please wait for your current requests to be completed or released.</p>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('resident.submit-request') }}" class="space-y-5" x-data="{ submitting: false, otherPurposeVisible: {{ old('purpose_other') ? 'true' : 'false' }}, limitReached: {{ $limitReached ? 'true' : 'false' }} }" @@submit="submitting = true">
            @csrf

            <div>
                <label for="document_type_id" class="label">Document Type <span class="text-red-600 dark:text-red-400">*</span></label>
                <select id="document_type_id" name="document_type_id" class="select-field" required autocomplete="off">
                    <option value="">Select Document</option>
                    @foreach($documentTypes as $doc)
                        <option value="{{ $doc->id }}" {{ old('document_type_id') == $doc->id ? 'selected' : '' }}
                            {{ in_array($doc->id, $activeDocumentTypeIds ?? []) ? 'disabled' : '' }}>
                            {{ $doc->name }} ({{ number_format($doc->processing_fee, 2) }})
                            @if(in_array($doc->id, $activeDocumentTypeIds ?? []))
                                — Already Requested
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('document_type_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="purpose_id" class="label">Purpose <span class="text-red-600 dark:text-red-400">*</span></label>
                <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off" @@change="otherPurposeVisible = $event.target.options[$event.target.selectedIndex]?.text === 'Others'">
                    <option value="">Select Purpose</option>
                    @foreach($purposes as $purpose)
                        <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                    @endforeach
                </select>
                @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div id="other-purpose-container" x-show="otherPurposeVisible" x-cloak>
                <label for="purpose_other" class="label">Specify Purpose</label>
                <input id="purpose_other" type="text" name="purpose_other" value="{{ old('purpose_other') }}"
                    class="input-field uppercase-input" placeholder="Please specify your purpose" autocomplete="off">
            </div>

            <button type="submit" class="btn-primary w-full" :disabled="submitting || limitReached" x-cloak>
                <span x-show="!submitting && !limitReached">Submit Request</span>
                <span x-show="limitReached">Request Limit Reached</span>
                <span x-show="submitting && !limitReached">Submitting...</span>
            </button>
        </form>
    </div>
</div>
@endsection
