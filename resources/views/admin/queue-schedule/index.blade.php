@extends('layouts.app')

@section('title', 'Queue Schedule Settings')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Queue Schedule Settings</h1>
        @if(! $schedule)
            <span class="text-sm text-gray-500 dark:text-gray-400">No schedule configured yet.</span>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-600/10 text-red-600 dark:text-red-400 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($schedule)
        <div class="card p-6 mb-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Daily Schedule</h2>
            <form method="POST" action="{{ route('admin.queue-schedule.update', $schedule->id) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label">Cut-off Time (Asia/Manila)</label>
                    <input type="time" name="cut_off_time" value="{{ $schedule->cut_off_time ? \Carbon\Carbon::parse($schedule->cut_off_time)->format('H:i') : '' }}"
                        class="input-field" autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">Requests at or after this time are scheduled for the next working day.</p>
                </div>

                <div>
                    <label class="label">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ $schedule->opening_time ? \Carbon\Carbon::parse($schedule->opening_time)->format('H:i') : '' }}"
                        class="input-field" autocomplete="off">
                </div>

                <div>
                    <label class="label">Max Requests Per Day</label>
                    <input type="number" name="max_requests_per_day" min="1"
                        value="{{ $schedule->max_requests_per_day }}"
                        placeholder="Unlimited" class="input-field" autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited daily requests.</p>
                </div>

                <div class="sm:col-span-2 flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="cut_off_enabled" value="1" {{ $schedule->cut_off_enabled ? 'checked' : '' }} class="rounded">
                        Cut-off enabled
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" value="1" {{ $schedule->is_active ? 'checked' : '' }} class="rounded">
                        Schedule active
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary text-sm">Save Schedule</button>
                </div>
            </form>
        </div>

        <div class="card p-6 mb-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Non-Working Dates / Holidays</h2>

            <form method="POST" action="{{ route('admin.queue-schedule.holidays.store', $schedule->id) }}" class="flex flex-wrap items-end gap-3 mb-4">
                @csrf
                <div class="flex-1 min-w-[140px]">
                    <label class="label">Date</label>
                    <input type="date" name="holiday_date" class="input-field" required autocomplete="off">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="label">Name</label>
                    <input type="text" name="name" class="input-field" placeholder="e.g., Christmas Day" required autocomplete="off">
                </div>
                <button type="submit" class="btn-primary text-sm">Add</button>
            </form>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 font-medium">Date</th>
                        <th class="pb-2 font-medium">Name</th>
                        <th class="pb-2 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedule->holidays()->orderByDesc('holiday_date')->get() as $holiday)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2">{{ $holiday->holiday_date->format('M d, Y') }}</td>
                            <td class="py-2">{{ $holiday->name }}</td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('admin.queue-schedule.holidays.destroy', [$schedule->id, $holiday->id]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm" onclick="return confirm('Remove this non-working date?')">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">No non-working dates added.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection