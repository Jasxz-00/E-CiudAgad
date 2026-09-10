@extends('layouts.app')

@section('title', 'New Document Request')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('resident.requests') }}" wire:navigate class="btn-ghost p-2" aria-label="Go back">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('common.new_request') }}</h1>
    </div>

    <div class="card">
        <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-xl text-sm text-primary-700 dark:text-primary-400">
            <p class="font-medium">Hi, {{ $resident->first_name }}!</p>
            <p>{{ __('common.select_document_purpose') }}</p>
        </div>

        <div class="mb-6 p-4 {{ $limitReached ? 'bg-red-600/10 border border-danger/30 text-red-600 dark:text-red-400' : 'bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400' }} rounded-xl text-sm">
            <p>
                {{ __('common.active_requests_count', ['current' => $activeCount, 'max' => $maxActiveRequests]) }}
            </p>
            @if($limitReached)
                <p class="mt-1 font-medium">{{ __('common.limit_reached') }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('resident.submit-request') }}" class="space-y-5" x-data="{ submitting: false, otherPurposeVisible: {{ old('purpose_other') ? 'true' : 'false' }}, limitReached: {{ $limitReached ? 'true' : 'false' }}, selectedDocType: {{ old('document_type_id') ?: 'null' }}, purposeContainerVisible: {{ old('purpose_id') ? 'true' : 'false' }}, certResidencyId: {{ json_encode($certResidencyId ?? 0) }}, documentTypesMap: {{ Js::from($documentTypes->pluck('code', 'id')) }}, selectedPurposeId: '{{ old('purpose_id') }}' }" @@submit="submitting = true">
            @csrf

            <div>
                <label for="document_type_id" class="label">{{ __('common.document_type') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                <select id="document_type_id" name="document_type_id" class="select-field" required autocomplete="off"
                    x-model="selectedDocType"
                    @@change="purposeContainerVisible = false; otherPurposeVisible = false">
                    <option value="">{{ __('registration.select_document') }}</option>
                    @foreach($documentTypes as $doc)
                        @if($doc->is_active)
                            <option value="{{ $doc->id }}" {{ old('document_type_id') == $doc->id ? 'selected' : '' }}
                                {{ in_array($doc->id, $activeDocumentTypeIds ?? []) ? 'disabled' : '' }}>
                                {{ $doc->name }}
                                @if(in_array($doc->id, $activeDocumentTypeIds ?? []))
                                    — {{ __('common.already_requested') }}
                                @endif
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('document_type_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div id="predefined-purpose-container" x-show="selectedDocType && selectedDocType != '' && selectedDocType != certResidencyId" x-cloak>
                <label for="purpose_id" class="label">{{ __('common.purpose') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off" x-model="selectedPurposeId">
                    <option value="">{{ __('registration.select_purpose') }}</option>
                    @foreach($purposes as $purpose)
                        <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                    @endforeach
                </select>
                @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div id="other-purpose-container" x-show="otherPurposeVisible || selectedDocType == certResidencyId" x-cloak>
                <label for="purpose_other" class="label">{{ __('registration.purpose_other') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                <input id="purpose_other" type="text" name="purpose_other" value="{{ old('purpose_other') }}"
                    class="input-field uppercase-input" placeholder="{{ __('registration.purpose_other_placeholder') }}" autocomplete="off">
                @error('purpose_other') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full" :disabled="submitting || limitReached" x-cloak>
                <span x-show="!submitting && !limitReached">{{ __('common.submit_request') }}</span>
                <span x-show="limitReached">{{ __('common.limit_reached') }}</span>
                <span x-show="submitting && !limitReached">{{ __('common.submitting') }}</span>
            </button>
        </form>
    </div>
</div>
@endsection
