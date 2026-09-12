@extends('layouts.app')

@section('title', 'Queue Monitor')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-8">
    <div class="max-w-4xl w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">BARANGAY MOLINO 1</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('registration.document_queue_system') }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2" id="current-date-time"></p>
            <p class="text-sm font-medium mt-1">
                <span class="text-gray-600 dark:text-gray-400">{{ __('registration.processing_date') }}:</span>
                <span class="text-gray-900 dark:text-gray-100 font-bold">{{\Carbon\Carbon::parse($queueStatus['service_date'])->format('M d, Y')}}</span>
            </p>
        </div>

        <div class="card p-8 mb-6">
            @if($queueStatus['cut_off_enabled'])
            <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                <span id="queue-status-badge" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold {{ $queueStatus['is_open'] ? 'bg-green-600/15 text-green-700 dark:text-green-300' : 'bg-accent-600/15 text-accent-700 dark:text-accent-300' }}">
                    <span class="w-2 h-2 rounded-full {{ $queueStatus['is_open'] ? 'bg-green-500' : 'bg-accent-600' }}"></span>
                    {{ $queueStatus['is_open'] ? __('registration.queue_open') : __('registration.queue_closed') }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @if($queueStatus['cut_off_time'])
                        {{ __('registration.cut_off_time_label', ['time' => \Carbon\Carbon::parse($queueStatus['cut_off_time'])->format('h:i A')]) }}
                    @endif
                    @if($queueStatus['max_requests_per_day'])
                        &middot; {{ $queueStatus['max_requests_per_day'] }}/day max
                    @endif
                </span>
            </div>
            @if(! $queueStatus['is_open'])
            <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 rounded-xl text-center text-sm text-accent-700 dark:text-accent-300">
                {{ __('registration.queue_closed_message') }}
            </div>
            @endif
            @endif

            <div class="text-center mb-6">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOW SERVING</p>
                <p class="text-7xl font-bold text-primary-700 dark:text-primary-400 mt-2 font-mono" id="now-serving">
                    @if($nowServing)
                        {{ $nowServing->queue_number }}
                    @else
                        —
                    @endif
                </p>
                <p class="text-lg text-gray-600 dark:text-gray-400 mt-2" id="now-serving-detail">{{ $nowServing ? ($nowServing->documentType?->name . ' — Window ' . ($nowServing->queue_position ?? 1)) : 'Waiting...' }}</p>
            </div>

            <div class="mt-8">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">NEXT UP</p>
                <div class="flex flex-wrap gap-4 justify-center" id="next-up">
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
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('registration.requests_remaining', ['count' => $remainingCount]) }} <span id="remaining-count" class="hidden">{{ $remainingCount }}</span></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('registration.last_updated') }}: <span id="last-updated">{{ now()->format('h:i:s A') }}</span></p>
            <div id="poll-error" class="hidden mt-2 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ __('registration.queue_refresh_failed') }}</div>
            <button type="button" onclick="refreshQueue(true)" class="btn-ghost mt-3 text-sm">{{ __('registration.manual_refresh') }}</button>
        </div>
    </div>
</div>
@push('scripts')
<script>
function renderQueue(data) {
    var nowServing = data.now_serving;
    document.getElementById('now-serving').textContent = nowServing ? nowServing.queue_number : '—';
    document.getElementById('now-serving-detail').textContent = nowServing ? (nowServing.document + ' — Window ' + nowServing.window) : 'Waiting...';

    var nextUpEl = document.getElementById('next-up');
    nextUpEl.innerHTML = '';
    (data.next_up || []).forEach(function (req) {
        var box = document.createElement('div');
        box.className = 'bg-gray-100 dark:bg-gray-800 rounded-xl px-6 py-4 text-center min-w-[120px]';
        var num = document.createElement('p');
        num.className = 'text-3xl font-bold text-gray-700 dark:text-gray-300 font-mono';
        num.textContent = req.queue_number;
        var doc = document.createElement('p');
        doc.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1';
        doc.textContent = req.document || '';
        box.appendChild(num);
        box.appendChild(doc);
        nextUpEl.appendChild(box);
    });

    var remainingText = '{{ __('registration.requests_remaining', ['count' => '__COUNT__']) }}';
    document.querySelector('.text-center .text-sm.text-gray-600').textContent = remainingText.replace('__COUNT__', data.remaining_count);

    var badge = document.getElementById('queue-status-badge');
    if (badge) {
        var isOpen = data.queue && data.queue.is_open;
        badge.className = 'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold ' + (isOpen ? 'bg-green-600/15 text-green-700 dark:text-green-300' : 'bg-accent-600/15 text-accent-700 dark:text-accent-300');
        badge.innerHTML = '<span class="w-2 h-2 rounded-full ' + (isOpen ? 'bg-green-500' : 'bg-accent-600') + '"></span> ' + (isOpen ? '{{ __('registration.queue_open') }}' : '{{ __('registration.queue_closed') }}');
    }

    document.getElementById('last-updated').textContent = data.last_updated ? new Date(data.last_updated).toLocaleTimeString('en-US', {hour12: false}) : new Date().toLocaleTimeString('en-US', {hour12: false});
}

function refreshQueue(force) {
    if (force === undefined) {
        if (document.hidden) return;
    }
    fetch('{{ route('queue.status') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function (res) {
        if (!res.ok) throw new Error('bad status ' + res.status);
        return res.json();
    })
    .then(function (data) {
        document.getElementById('poll-error').classList.add('hidden');
        if (data.ok) renderQueue(data);
    })
    .catch(function () {
        document.getElementById('poll-error').classList.remove('hidden');
    });
}

function updateDateTime() {
    var now = new Date();
    document.getElementById('current-date-time').textContent = now.toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) + ' ' + now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
}
updateDateTime();
setInterval(updateDateTime, 1000);
setInterval(refreshQueue, 7000);
</script>
@endpush
@endsection