@extends('layouts.app')

@section('title', 'My Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="My Requests"
        actionUrl="{{ route('resident.new-request') }}"
        actionLabel="New Request"
        actionIcon="M12 4v16m8-8H4" />

    <x-card :padding="false">
        @if($requests->isEmpty())
            <x-empty-state
                title="No requests found"
                description="Submit a new document request to get started."
                actionUrl="{{ route('resident.new-request') }}"
                actionLabel="New Request" />
        @else
            <x-table :headers="['Queue #', 'Document', 'Purpose', 'Status', 'Queue Pos.', 'Date', 'Action']">
                @foreach($requests as $req)
                <tr>
                    <td class="font-mono">{{ $req->queue_number }}</td>
                    <td>{{ $req->documentType?->name }}</td>
                    <td>{{ $req->purpose?->name }}</td>
                    <td><x-badge :status="$req->status" /></td>
                    <td>{{ $req->queue_position ?? '-' }}</td>
                    <td class="text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('resident.request.show', $req->id) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">View</a>
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
