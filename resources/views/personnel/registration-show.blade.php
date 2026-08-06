@extends('layouts.app')

@section('title', 'Registration Result')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
            <p class="text-sm text-accent-700 dark:text-accent-300 dark:text-accent-300 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Resident Registered Successfully</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Tracking Number</span>
                <p class="font-mono font-medium text-lg text-primary-700 dark:text-primary-400">{{ $resident->user->tracking_number }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Full Name</span>
                <p class="font-medium">{{ $resident->full_name }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Contact Number</span>
                <p class="font-medium">{{ $resident->contact_number }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Address</span>
                <p class="font-medium">{{ $resident->full_address }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Category</span>
                <p class="font-medium capitalize">{{ $resident->category }}</p>
            </div>
            <div class="sm:col-span-2">
                <span class="text-gray-600 dark:text-gray-400">Document Requests</span>
                @if($resident->documentRequests->isNotEmpty())
                    @foreach($resident->documentRequests as $req)
                        <p class="font-medium font-mono">{{ $req->queue_number }} - <span class="badge-{{ $req->status }}">{{ ucfirst($req->status) }}</span></p>
                    @endforeach
                @else
                    <p class="text-gray-600 dark:text-gray-400">No requests filed yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('personnel.registrations.create') }}" class="btn-primary">Register Another Resident</a>
        <a href="{{ route('personnel.dashboard') }}" class="btn-ghost">Back to Dashboard</a>
    </div>
</div>
@endsection
