@extends('layouts.app')

@section('title', 'Profile Change Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Profile Change Approvals</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['pending' => 'Pending ('.$counts['pending'].')', 'approved' => 'Approved ('.$counts['approved'].')', 'rejected' => 'Rejected ('.$counts['rejected'].')', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.profile-changes.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === $key ? 'bg-accent-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($changes as $change)
            <div class="card" x-data="{ open: false }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $change->resident?->full_name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $change->created_at->format('M d, Y h:i A') }} &middot; Changing: {{ implode(', ', $change->changed_fields) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $change->status === 'pending' ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : ($change->status === 'approved' ? 'bg-green-600/10 text-green-600 dark:text-green-400' : 'bg-red-600/10 text-red-600 dark:text-red-400') }}">
                            {{ ucfirst($change->status) }}
                        </span>
                        <button type="button" @@click="open = !open" class="btn-ghost text-sm px-3 py-1.5">Diff</button>
                    </div>
                </div>

                <div x-show="open" x-cloak class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                    @php
                        $labelMap = [
                            'contact_number' => 'Contact Number', 'emergency_contact' => 'Emergency Contact',
                            'civil_status' => 'Civil Status', 'religion' => 'Religion', 'place_of_birth' => 'Place of Birth',
                            'occupation' => 'Occupation', 'street' => 'Street', 'barangay' => 'Barangay',
                            'subdivision' => 'Subdivision', 'house_no' => 'House No.', 'phase' => 'Phase No.', 'building_no' => 'Building No.',
                            'unit_no' => 'Unit No.', 'city' => 'City', 'province' => 'Province', 'zip_code' => 'Zip Code',
                            'email' => 'Email Address',
                        ];
                        $merged = array_merge($change->old_data['resident'] ?? [], ($change->new_data['email'] !== null && array_key_exists('email', $change->new_data) ? ['email' => $change->new_data['email']] : []));
                    @endphp
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-left text-gray-600 dark:text-gray-400">
                                <th class="py-2 pr-4">Field</th>
                                <th class="py-2 pr-4">Current</th>
                                <th class="py-2">Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($change->new_data['resident'] ?? [] as $field => $newValue)
                                @php $oldValue = $change->old_data['resident'][$field] ?? null; @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2 pr-4 font-medium">{{ $labelMap[$field] ?? $field }}</td>
                                    <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 line-through">{{ $oldValue }}</td>
                                    <td class="py-2 text-green-700 dark:text-green-300">{{ $newValue }}</td>
                                </tr>
                            @endforeach
                            @if(! empty($change->new_data['email']))
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2 pr-4 font-medium">Email Address</td>
                                    <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 line-through">{{ $change->old_data['email'] ?? '—' }}</td>
                                    <td class="py-2 text-green-700 dark:text-green-300">{{ $change->new_data['email'] }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @if($change->status === 'pending')
                        <div class="mt-4 flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('admin.profile-changes.approve', $change->id) }}">
                                @csrf
                                <button type="submit" class="btn-primary text-sm">Approve & Apply</button>
                            </form>
                            <form method="POST" action="{{ route('admin.profile-changes.reject', $change->id) }}" onsubmit="return confirm('Reject this profile change?')">
                                @csrf
                                <input type="hidden" name="rejection_reason" value="Rejected by admin.">
                                <button type="submit" class="btn-danger text-sm">Reject</button>
                            </form>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ ucfirst($change->status) }} on {{ $change->reviewed_at?->format('M d, Y h:i A') }}
                            by {{ $change->reviewedBy?->username ?? '—' }}
                            @if($change->rejection_reason && $change->status === 'rejected')
                                &middot; Reason: {{ $change->rejection_reason }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="card text-center text-gray-600 dark:text-gray-400 py-8">
                No profile change requests found.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $changes->links() }}
    </div>
</div>
@endsection