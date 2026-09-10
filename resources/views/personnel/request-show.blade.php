@extends('layouts.app')

@section('title', __('common.requests') . ' ' . $request->queue_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('personnel.requests') }}" class="btn-ghost mb-4">&larr; {{ __('common.back') }}</a>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-bold">{{ $request->queue_number }}</h2>
                    <span class="badge-{{ $request->status }} text-sm px-3 py-1">{{ __("common.{$request->status}") }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 sm:gap-4 text-sm">
                    <div><span class="text-gray-600 dark:text-gray-400">{{ __('common.document') }}</span><p class="font-medium">{{ $request->documentType?->name }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">{{ __('common.purpose') }}</span><p class="font-medium">{{ $request->purpose?->name }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">{{ __('common.queue_position') }}</span><p class="font-medium">{{ $request->queue_position ?? '-' }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">{{ __('common.weight') }}</span><p class="font-medium">{{ number_format((float) $request->total_weight, 2) }}</p></div>
                    <div><span class="text-gray-600 dark:text-gray-400">{{ __('common.date') }}</span><p class="font-medium">{{ $request->created_at->format('M d, Y h:i A') }}</p></div>
                    @if($request->completed_at)<div><span class="text-gray-600 dark:text-gray-400">{{ __('common.completed') }}</span><p class="font-medium">{{ $request->completed_at->format('M d, Y h:i A') }}</p></div>@endif
                </div>

                @if($request->rejection_reason)
                <div class="mt-4 p-3 bg-red-600/10 rounded-xl text-sm text-red-600 dark:text-red-400">
                    <strong>{{ __('common.rejected') }}:</strong> {{ $request->rejection_reason }}
                </div>
                @endif
            </div>

            <div class="card" x-data="{ showRejectForm: false }">
                <h3 class="font-semibold mb-3">{{ __('common.action') }}s</h3>
                <div class="flex flex-wrap gap-3">
                    @if(in_array($request->status, ['pending']))
                    <form method="POST" action="{{ route('personnel.request.review', $request->id) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-sm">{{ __('common.reviewing') }}</button>
                    </form>
                    @endif

                    @if(in_array($request->status, ['reviewing']))
                    <form method="POST" action="{{ route('personnel.request.approve', $request->id) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-sm">{{ __('common.approved') }}</button>
                    </form>
                    <button @@click="showRejectForm = !showRejectForm" class="btn-danger text-sm">{{ __('common.rejected') }}</button>
                    @endif

                    @if(in_array($request->status, ['approved']))
                    <form method="POST" action="{{ route('personnel.request.complete', $request->id) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-sm">{{ __('common.completed') }}</button>
                    </form>
                    @endif

                    @if(in_array($request->status, ['completed']))
                    <form method="POST" action="{{ route('personnel.request.release', $request->id) }}">
                        @csrf
                        <button type="submit" class="btn-accent text-sm">{{ __('common.released') }}</button>
                    </form>
                    @endif
                </div>

                <div x-show="showRejectForm" x-cloak x-transition:enter="transition ease-out duration-200" class="mt-4 p-4 bg-red-600/10 rounded-xl">
                    <form method="POST" action="{{ route('personnel.request.reject', $request->id) }}">
                        @csrf
                        <label for="rejection_reason" class="label">{{ __('common.rejected') }} {{ __('common.reason') }}</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" class="input-field" required placeholder="{{ __('common.rejected') }}..." autocomplete="off"></textarea>
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="btn-danger text-sm">{{ __('common.confirm') }}</button>
                            <button type="button" @@click="showRejectForm = false" class="btn-ghost text-sm">{{ __('common.cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card" x-data="{ openLightbox: null }">
                <h3 class="font-semibold mb-3">{{ __('common.verification_documents') }}</h3>
                <div class="space-y-4">
                    @if($request->resident?->idVerifications->isNotEmpty())
                        @foreach($request->resident->idVerifications as $idv)
                            @php
                                $frontUrl = $idv->file_path ? route('storage.private', ['path' => $idv->file_path]) : null;
                                $backUrl = $idv->back_file_path ? route('storage.private', ['path' => $idv->back_file_path]) : null;
                            @endphp
                            <div class="p-3 bg-gray-50 dark:bg-gray-950 rounded-xl">
                                <p class="text-sm font-medium mb-1">{{ __('common.id_verification') }} - {{ str_replace('_', ' ', ucfirst($idv->id_type)) }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ __('common.id_front') }}</p>
                                        @if($frontUrl)
                                            <button type="button" @@click="openLightbox = '{{ $frontUrl }}'" class="inline-flex items-center gap-1 text-xs text-primary-700 dark:text-primary-400 hover:underline">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                {{ __('common.view_photo') }}
                                            </button>
                                        @else
                                            <p class="text-xs text-gray-600 dark:text-gray-400 italic">{{ __('common.file_unavailable') }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ __('common.id_back') }}</p>
                                        @if($backUrl)
                                            <button type="button" @@click="openLightbox = '{{ $backUrl }}'" class="inline-flex items-center gap-1 text-xs text-primary-700 dark:text-primary-400 hover:underline">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                {{ __('common.view_photo') }}
                                            </button>
                                        @else
                                            <p class="text-xs text-gray-600 dark:text-gray-400 italic">{{ __('common.file_unavailable') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('common.no_id_uploaded') }}</p>
                    @endif

                    @if($request->resident?->status_verification_photo)
                        @php
                            $statusPhotoUrl = route('storage.private', ['path' => $request->resident->status_verification_photo]);
                        @endphp
                        <div class="p-3 bg-gray-50 dark:bg-gray-950 rounded-xl">
                            <p class="text-sm font-medium mb-1">{{ __('common.status_verification_photo') }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ __('common.category') }}: <span class="capitalize">{{ $request->resident?->category }}</span></p>
                            <button type="button" @@click="openLightbox = '{{ $statusPhotoUrl }}'" class="inline-flex items-center gap-1 text-xs text-primary-700 dark:text-primary-400 hover:underline">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ __('common.view_photo') }}
                            </button>
                        </div>
                    @endif

                    <div class="text-xs text-gray-600 dark:text-gray-400 italic">
                        {{ __('common.access_control_note') }}
                    </div>
                </div>

                <div x-show="openLightbox" x-cloak
                     @@keydown.escape.window="openLightbox = null"
                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4"
                     @@click.self="openLightbox = null">
                    <div class="relative max-w-2xl w-full">
                        <button type="button" @@click="openLightbox = null" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-sm font-medium flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            {{ __('common.close') }}
                        </button>
                        <img :src="openLightbox" alt="Verification document" class="w-full h-auto rounded-xl shadow-2xl">
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card">
                <h3 class="font-semibold mb-3">{{ __('common.resident') }}</h3>
                <div class="text-sm space-y-2">
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.resident_name') }}:</span> {{ $request->resident?->full_name ?? 'N/A' }}</p>
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.age') }}:</span> {{ $request->resident?->age }}</p>
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.gender') }}:</span> <span class="capitalize">{{ $request->resident?->gender }}</span></p>
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.category') }}:</span> <span class="capitalize">{{ $request->resident?->category }}</span></p>
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.contact') }}:</span> {{ $request->resident?->contact_number }}</p>
                    <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.address') }}:</span> {{ $request->resident?->full_address }}</p>
                </div>
            </div>

            <div class="card">
                <h3 class="font-semibold mb-3">{{ __('common.id_verification') }}</h3>
                @if($request->resident?->idVerifications->isNotEmpty())
                    @foreach($request->resident->idVerifications as $idv)
                    <div class="text-sm space-y-1">
                        <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.type') }}:</span> {{ str_replace('_', ' ', ucfirst($idv->id_type)) }}</p>
                        <p><span class="text-gray-600 dark:text-gray-400">{{ __('common.status') }}:</span> {!! $idv->is_verified ? '<span class="text-green-600 dark:text-green-400 font-medium">'.__('common.verified').'</span>' : '<span class="text-accent-700 dark:text-accent-300 font-medium">'.__('common.pending').'</span>' !!}</p>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('common.no_id_uploaded') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


