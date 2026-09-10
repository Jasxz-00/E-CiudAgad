@extends('layouts.app')

@section('title', __('common.preview').' - '.$documentRequest->queue_number)

@section('content')
<div class="max-w-5xl mx-auto pb-16">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $documentRequest->resident->full_name }} &middot; {{ $documentRequest->documentType->name }}</p>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $documentRequest->queue_number }}
                @if ($issued)
                    <span class="ml-2 text-sm font-mono text-accent-600 dark:text-accent-300">{{ $issued->control_number }}</span>
                @else
                    <span class="ml-2 text-sm text-gray-400 dark:text-gray-500">(control number assigned upon completion)</span>
                @endif
            </h1>
        </div>
        <div class="flex gap-3 no-print">
            <a href="{{ route('personnel.request.show', $documentRequest->id) }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                &larr; {{ __('common.back') }}
            </a>
            @if ($issued)
                <button type="button" onclick="printDocument()" class="inline-flex items-center px-4 py-2 rounded-lg bg-accent-600 hover:bg-accent-700 text-white text-sm font-medium">
                    {{ __('common.print') }}
                </button>
            @endif
        </div>
    </div>

    @if ($issued)
        <div class="mb-4 no-print rounded-lg border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/30 px-4 py-2 text-sm text-green-800 dark:text-green-200">
            {{ __('documents.completed_notice', ['control' => $issued->control_number]) }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 print:p-0 print:border-0 print:dark:bg-white">
        <div id="document-sheet" class="mx-auto" style="max-width:816px;">
            {!! $svg !!}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function printDocument() {
        const url = @json(route('personnel.documents.print', $issued?->id ?? 0));
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (url && ! url.endsWith('/0')) {
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token ?? '', 'Accept': 'application/json' },
            }).catch(function () {});
        }
        setTimeout(function () { window.print(); }, 250);
    }
</script>
<style>
    @@media print {
        @@page { size: A4; margin: 0; }
        body * { visibility: hidden; }
        #document-sheet, #document-sheet * { visibility: visible; }
        #document-sheet {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        .no-print { display: none !important; }
    }
</style>
@endpush