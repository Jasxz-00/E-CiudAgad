@extends('layouts.app')

@section('title', 'Queue Monitor')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-8">
    <div class="max-w-4xl w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">BARANGAY MOLINO 1</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('registration.document_queue_system') }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2" id="current-date-time"></p>
        </div>

        <div class="card p-8 mb-6">
            <div class="text-center mb-6">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOW SERVING</p>
                <p class="text-7xl font-bold text-primary-700 dark:text-primary-400 mt-2 font-mono" id="now-serving">
                    @if($nowServing)
                        {{ $nowServing->queue_number }}
                    @else
                        —
                    @endif
                </p>
                <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">{{ $nowServing ? ($nowServing->documentType?->name . ' — Window ' . ($nowServing->queue_position ?? 1)) : 'Waiting...' }}</p>
            </div>

            <div class="mt-8">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">NEXT UP</p>
                <div class="flex flex-wrap gap-4 justify-center">
                    @foreach($nextUp as $req)
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl px-6 py-4 text-center min-w-[120px]">
                            <p class="text-3xl font-bold text-gray-700 dark:text-gray-300 font-mono">{{ $req->queue_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $req->documentType?->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('registration.requests_remaining', ['count' => $remainingCount]) }}</p>
        </div>
    </div>
</div>
@push('scripts')
<script>
function updateDateTime() {
    var now = new Date();
    document.getElementById('current-date-time').textContent = now.toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) + ' ' + now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
}
updateDateTime();
setInterval(updateDateTime, 1000);
</script>
@endpush
@endsection
