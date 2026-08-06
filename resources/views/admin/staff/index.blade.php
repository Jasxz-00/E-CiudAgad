@extends('layouts.app')

@section('title', 'Staff Accounts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="Staff Accounts" description="Manage personnel accounts"
        actionUrl="{{ route('admin.users.create') }}?return=staff" actionLabel="Add Staff"
        actionIcon="M12 4v16m8-8H4" />

    @if(session('success'))
        <x-alert type="success" :message="session('success')" dismissible />
    @endif

    <form method="GET" action="{{ route('admin.staff.index') }}" class="mb-6">
        <x-card>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <input type="text" name="search" placeholder="Search username or email..." value="{{ request('search') }}" class="input-field">
                </div>
                <div>
                    <select name="status" class="select-field">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Search</button>
                    <a href="{{ route('admin.staff.index') }}" class="btn-ghost flex-1 text-center">Clear</a>
                </div>
            </div>
        </x-card>
    </form>

    <x-card :padding="false">
        <x-table :headers="['Username', 'Email', 'Status', 'Created', 'Actions']">
            @forelse($users as $user)
            <tr>
                <td class="font-mono">{{ $user->username ?? '-' }}</td>
                <td class="max-w-[14rem] truncate">{{ $user->email }}</td>
                <td>
                    @if($user->is_active)
                        <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                    @else
                        <span class="text-red-600 dark:text-red-400 font-medium">Inactive</span>
                    @endif
                </td>
                <td class="text-gray-600 dark:text-gray-400">{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="flex gap-3 items-center">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">View</a>
                        <a href="{{ route('admin.users.edit', $user) }}?return=staff" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">Edit</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-600 dark:text-gray-400">No staff accounts found.</td>
            </tr>
            @endforelse
        </x-table>
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $users->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
