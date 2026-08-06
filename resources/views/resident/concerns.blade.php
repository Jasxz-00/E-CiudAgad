@extends('layouts.app')

@section('title', __('common.concerns'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="{{ __('common.submit_concern') }}" />

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/15 text-green-600 dark:text-green-400 border border-success/30 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <x-card class="mb-6">
        <form method="POST" action="{{ route('resident.concerns.store') }}" class="space-y-4" x-data="{ submitting: false }" @@submit="submitting = true">
            @csrf

            <div>
                <label for="subject" class="label">{{ __('common.subject') }}</label>
                <input id="subject" type="text" name="subject"
                    value="{{ old('subject') }}" required
                    class="input-field @error('subject') input-error @enderror"
                    placeholder="{{ __('common.subject') }}" autocomplete="off">
                @error('subject') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="label">{{ __('common.message') }}</label>
                <textarea id="message" name="message" rows="5" required
                    class="input-field @error('message') input-error @enderror"
                    placeholder="{{ __('common.message') }}" autocomplete="off">{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary" :disabled="submitting">
                <span x-show="!submitting">{{ __('common.submit_concern') }}</span>
                <span x-show="submitting">{{ __('common.loading') }}</span>
            </button>
        </form>
    </x-card>

    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.concerns') }}</h2>
        </div>

        @if($concerns->isEmpty())
            <x-empty-state
                title="{{ __('common.no_data') }}"
                description="{{ __('common.submit_concern') }}" />
        @else
            <div class="space-y-4">
                @foreach($concerns as $concern)
                    <a href="{{ route('resident.concerns.show', $concern->id) }}" wire:navigate class="block p-4 bg-gray-50 dark:bg-gray-950 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $concern->subject }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ Str::limit($concern->message, 200) }}</p>
                                @if($concern->admin_notes)
                                    <div class="mt-3 p-3 bg-accent-900/20 border border-accent-200 dark:border-accent-800/30 rounded-lg">
                                        <p class="text-xs text-accent-400 font-medium mb-1">{{ __('common.track_status') }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $concern->admin_notes }}</p>
                                    </div>
                                @endif
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">{{ $concern->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <x-badge :status="$concern->status" class="shrink-0" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-card>
</div>
@endsection
