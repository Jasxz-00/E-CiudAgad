@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @php $back = request('return', 'users'); @endphp
    <a href="{{ $back === 'accounts' ? route('admin.accounts.index') : ($back === 'staff' ? route('admin.staff.index') : route('admin.users.index')) }}" class="btn-ghost mb-4">&larr; Back</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Create {{ $back === 'accounts' ? 'Admin' : ($back === 'staff' ? 'Staff' : '') }} User</h1>

    <x-card>
        <form method="POST" action="{{ route('admin.users.store', ['return' => $back]) }}" class="space-y-4">
            @csrf
            <x-input name="email" label="Email" type="email" required autocomplete="email" />
            <x-input name="username" label="Username" autocomplete="username" />
            <x-input name="password" label="Password" type="password" required autocomplete="new-password" />
            <x-select name="role" label="Role" required :options="['resident' => 'Resident', 'personnel' => 'Personnel', 'admin' => 'Admin']" :value="$back === 'accounts' ? 'admin' : ($back === 'staff' ? 'personnel' : '')" autocomplete="off" />
            <button type="submit" class="btn-primary">Create User</button>
        </form>
    </x-card>
</div>
@endsection
