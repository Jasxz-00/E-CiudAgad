@extends('layouts.app')

@section('title', __('common.requests'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="{{ __('common.requests') }}"
        actionUrl="{{ route('resident.new-request') }}"
        actionLabel="{{ __('common.new_request') }}"
        actionIcon="M12 4v16m8-8H4" />

    <x-card :padding="false">
        @if($requests->isEmpty())
            <x-empty-state
                title="{{ __('common.no_requests') }}"
                description="{{ __('common.new_request') }}"
                actionUrl="{{ route('resident.new-request') }}"
                actionLabel="{{ __('common.new_request') }}" />
        @else
            <x-table :headers="[__('common.queue_number'), __('common.document'), __('common.purpose'), __('common.status'), __('common.queue_position'), __('common.date'), __('common.action')]">
                @foreach($requests as $req)
                <tr>
                    <td class="font-mono">{{ $req->queue_number }}</td>
                    <td>{{ $req->documentType?->name }}</td>
                    <td>{{ $req->purpose?->name }}</td>
                    <td><x-badge :status="$req->status" /></td>
                    <td>{{ $req->queue_position ?? __('common.queue_position_na') }}</td>
                    <td class="text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('resident.request.show', $req->id) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">{{ __('common.view') }}</a>
                    </td>
                </tr>
                @endforeach
            </x-table>
            @if($requests->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $requests->links() }}</div>
            @endif
        @endif
    </x-card>
</div>
@endsection
