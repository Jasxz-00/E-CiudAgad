@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @php $back = request('return', 'users'); @endphp
    <a href="{{ $back === 'accounts' ? route('admin.accounts.index') : ($back === 'staff' ? route('admin.staff.index') : route('admin.users.index')) }}" class="btn-ghost mb-4">&larr; Back</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">User Profile</h1>

    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Account Info</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 sm:gap-4 text-sm">
            <div><span class="text-gray-600 dark:text-gray-400">Username</span><p class="font-medium font-mono">{{ $user->username ?? '-' }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Email</span><p class="font-medium">{{ $user->email }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Role</span><p class="font-medium capitalize">{{ $user->role }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Status</span><p class="font-medium">{{ $user->is_active ? 'Active' : 'Inactive' }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Created</span><p class="font-medium">{{ $user->created_at->format('M d, Y h:i A') }}</p></div>
        </div>
    </div>

    @if($user->resident)
    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Personal Info</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 sm:gap-4 text-sm">
            <div><span class="text-gray-600 dark:text-gray-400">Full Name</span><p class="font-medium">{{ $user->resident->full_name }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Age / Gender</span><p class="font-medium">{{ $user->resident->age }} / {{ ucfirst($user->resident->gender) }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Category</span><p class="font-medium capitalize">{{ $user->resident->category }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Contact</span><p class="font-medium">{{ $user->resident->contact_number }}</p></div>
            <div class="col-span-2"><span class="text-gray-600 dark:text-gray-400">Address</span><p class="font-medium">{{ $user->resident->full_address }}</p></div>
        </div>
    </div>

    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Document Requests</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-600 dark:text-gray-400 border-b"><th class="pb-2">Queue</th><th class="pb-2">Document</th><th class="pb-2">Status</th><th class="pb-2">Date</th></tr></thead>
                <tbody>
                    @forelse($user->resident->documentRequests as $req)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 font-mono">{{ $req->queue_number }}</td>
                        <td class="py-2">{{ $req->documentType?->name }}</td>
                        <td class="py-2"><span class="badge-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="py-2 text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-600 dark:text-gray-400">No requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
