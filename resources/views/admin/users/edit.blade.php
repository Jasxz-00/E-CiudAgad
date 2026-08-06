@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @php $back = request('return', 'users'); @endphp
    <a href="{{ $back === 'accounts' ? route('admin.accounts.index') : ($back === 'staff' ? route('admin.staff.index') : route('admin.users.index')) }}" class="btn-ghost mb-4">&larr; Back</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Edit User: {{ $user->email }}</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', ['user' => $user, 'return' => request('return', 'users')]) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ $user->email }}" class="input-field" required autocomplete="email">
            </div>
            <div>
                <label for="username" class="label">Username</label>
                <input id="username" type="text" name="username" value="{{ $user->username }}" class="input-field" autocomplete="username">
            </div>
            <div>
                <label for="password" class="label">New Password <span class="text-xs text-gray-600 dark:text-gray-400">(leave blank to keep current)</span></label>
                <input id="password" type="password" name="password" class="input-field" autocomplete="new-password">
            </div>
            <div>
                <label for="role" class="label">Role</label>
                <select id="role" name="role" class="select-field" required autocomplete="off">
                    <option value="resident" {{ $user->role == 'resident' ? 'selected' : '' }}>Resident</option>
                    <option value="personnel" {{ $user->role == 'personnel' ? 'selected' : '' }}>Personnel</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                    <span class="text-sm">Active</span>
                </label>
            </div>
            <button type="submit" class="btn-primary">Update User</button>
        </form>
    </div>
</div>
@endsection
