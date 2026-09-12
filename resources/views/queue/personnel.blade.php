@extends('layouts.app')

@section('title', 'Queue Dashboard')

@section('content')
<div class="min-h-[calc(100vh-4rem)] px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('registration.queue_dashboard') }}</h1>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('registration.now_serving') }}: <span class="font-bold text-primary-700" id="now-serving-count">@if($nowServing){{ $nowServing->queue_number }}@else—@endif</span></span>
                <span id="personnel-poll-error" class="hidden p-2 bg-red-600/10 text-red-600 dark:text-red-400 rounded-lg text-xs">{{ __('registration.queue_refresh_failed') }}</span>
                <button type="button" onclick="refreshPersonnelQueue()" class="btn-ghost text-xs">{{ __('registration.manual_refresh') }}</button>
            </div>
        </div>

        @if($queueStatus['cut_off_enabled'])
        <div class="mb-6 p-4 {{ $queueStatus['is_open'] ? 'bg-green-600/10 border border-green-600/30 text-green-700 dark:text-green-300' : 'bg-accent-50 dark:bg-accent-900/20 border border-accent-600/30 text-accent-700 dark:text-accent-300' }} rounded-xl text-sm">
            <p class="font-medium">{{ __('registration.queue_status_label') }}: {{ $queueStatus['is_open'] ? __('registration.queue_open') : __('registration.queue_closed') }}
                @if($queueStatus['cut_off_time'])
                    &middot; {{ __('registration.cut_off_time_label', ['time' => \Carbon\Carbon::parse($queueStatus['cut_off_time'])->format('h:i A')]) }}
                @endif
                @if($queueStatus['max_requests_per_day'])
                    &middot; {{ __('registration.daily_capacity_label', ['count' => $queueStatus['max_requests_per_day']]) }}
                @endif
            </p>
            <p class="mt-1 text-xs">{{ $queueStatus['is_open'] ? __('registration.queue_open_personnel') : __('registration.queue_closed_personnel') }}</p>
        </div>
        @endif

        <div class="card overflow-hidden mb-8">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('registration.todays_queue') }} ({{ \Carbon\Carbon::parse($queueStatus['service_date'])->format('M d, Y') }})</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('registration.queue_number') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('registration.control_number') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.resident') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.document') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.purpose') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.status') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.date') }}</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($requests->where(fn ($r) => $r->service_date?->toDateString() === $queueStatus['service_date']) as $req)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-950">
                        <td class="px-4 py-3 font-mono font-bold">{{ $req->queue_number }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $req->control_number }}</td>
                        <td class="px-4 py-3">{{ $req->resident?->full_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $req->documentType?->name }}</td>
                        <td class="px-4 py-3">{{ $req->purpose?->name ?? $req->purpose_other ?? '-' }}</td>
                        <td class="px-4 py-3"><x-badge :status="$req->status" /></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('request.show', $req->control_number) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-block px-2 py-1">{{ __('common.view') }}</a>
                            @if($req->status === 'pending')
                                <form method="POST" action="{{ route('queue.call-next', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-700 dark:text-green-400 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.call_next') }}</button>
                                </form>
                                <form method="POST" action="{{ route('queue.hold', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-accent-700 dark:text-accent-300 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.hold') }}</button>
                                </form>
                                <form method="POST" action="{{ route('queue.skip', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-500 dark:text-gray-400 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.skip') }}</button>
                                </form>
                            @elseif($req->status === 'on_hold')
                                <form method="POST" action="{{ route('queue.resume', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-700 dark:text-green-400 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.resume') }}</button>
                                </form>
                            @elseif($req->status === 'reviewing')
                                <form method="POST" action="{{ route('queue.ready', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-accent-700 dark:text-accent-300 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.mark_ready') }}</button>
                                </form>
                            @elseif($req->status === 'ready_for_release')
                                <form method="POST" action="{{ route('queue.release', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-700 dark:text-green-400 hover:underline text-sm inline-block px-2 py-1">{{ __('common.released') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($requests->where(fn ($r) => $r->service_date?->toDateString() === $queueStatus['service_date'])->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">{{ __('registration.no_requests') }}</div>
            @endif
        </div>

        @if($futureScheduled->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('registration.future_scheduled') }}</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('registration.queue_number') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.resident') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.document') }}</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('registration.processing_date') }}</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($futureScheduled as $req)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-950">
                        <td class="px-4 py-3 font-mono font-bold">{{ $req->queue_number }}</td>
                        <td class="px-4 py-3">{{ $req->resident?->full_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $req->documentType?->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $req->service_date?->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('request.show', $req->control_number) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-block px-2 py-1">{{ __('common.view') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@push('scripts')
<script>
function refreshPersonnelQueue() {
    fetch('{{ route('queue.status') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(function (res) {
        if (!res.ok) throw new Error('bad status ' + res.status);
        return res.json();
    })
    .then(function (data) {
        document.getElementById('personnel-poll-error').classList.add('hidden');
        if (!data.ok) return;
        var el = document.getElementById('now-serving-count');
        if (el) el.textContent = data.now_serving ? data.now_serving.queue_number : '—';
    })
    .catch(function () {
        document.getElementById('personnel-poll-error').classList.remove('hidden');
    });
}
setInterval(function () { if (!document.hidden) refreshPersonnelQueue(); }, 7000);
</script>
@endpush
@endsection