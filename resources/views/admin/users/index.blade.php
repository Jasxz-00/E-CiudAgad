@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="Manage Users" actionUrl="{{ route('admin.users.create') }}" actionLabel="Add User"
        actionIcon="M12 4v16m8-8H4" />

    @if(session('success'))
        <x-alert type="success" :message="session('success')" dismissible />
    @endif

    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 sm:gap-4">
            <div class="sm:col-span-2">
                <input type="text" name="search" placeholder="Search username, name, or email..." value="{{ request('search') }}" class="input-field">
            </div>
            <div>
                <select name="role" class="select-field">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="personnel" {{ request('role') == 'personnel' ? 'selected' : '' }}>Personnel</option>
                    <option value="resident" {{ request('role') == 'resident' ? 'selected' : '' }}>Resident</option>
                </select>
            </div>
            <div>
                <select name="status" class="select-field">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <select name="category" class="select-field">
                    <option value="">All Categories</option>
                    <option value="regular" {{ request('category') == 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="senior" {{ request('category') == 'senior' ? 'selected' : '' }}>Senior</option>
                    <option value="pwd" {{ request('category') == 'pwd' ? 'selected' : '' }}>PWD</option>
                    <option value="pregnant" {{ request('category') == 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">Search</button>
                <a href="{{ route('admin.users.export', request()->query()) }}" class="btn-accent flex-1 text-center text-sm">Export</a>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table :headers="['Username', 'Name', 'Email', 'Role', 'Status', 'Created', 'Actions']">
            @forelse($users as $user)
            <tr>
                <td class="font-mono">{{ $user->username ?? '-' }}</td>
                <td>{{ $user->resident?->full_name ?? '-' }}</td>
                <td class="max-w-[14rem] truncate">{{ $user->email }}</td>
                <td class="capitalize">{{ $user->role }}</td>
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
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">Edit</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-8 text-gray-600 dark:text-gray-400">No users found.</td>
            </tr>
            @endforelse
        </x-table>
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $users->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
