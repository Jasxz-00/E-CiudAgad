@extends('layouts.app')

@section('title', 'Queue Dashboard')

@section('content')
<div class="min-h-[calc(100vh-4rem)] px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('registration.queue_dashboard') }}</h1>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('registration.now_serving') }}: <span class="font-bold text-primary-700" id="now-serving-count">@if($nowServing){{ $nowServing->queue_number }}@else—@endif</span></span>
        </div>

        <div class="card overflow-hidden">
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
                    @foreach($requests as $req)
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
                                <form method="POST" action="{{ route('queue.process', $req->control_number) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-700 dark:text-green-400 hover:underline text-sm inline-block px-2 py-1">{{ __('registration.process') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($requests->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">{{ __('registration.no_requests') }}</div>
            @endif
            @if($requests->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
