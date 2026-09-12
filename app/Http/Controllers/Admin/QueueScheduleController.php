<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\QueueSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueScheduleController extends Controller
{
    public function index()
    {
        $schedule = QueueSchedule::with('holidays')->first();

        return view('admin.queue-schedule.index', compact('schedule'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'cut_off_time' => ['nullable', 'date_format:H:i'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'max_requests_per_day' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'cut_off_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $schedule = QueueSchedule::findOrFail($id);
        $oldValues = $schedule->only([
            'cut_off_time',
            'opening_time',
            'max_requests_per_day',
            'cut_off_enabled',
            'is_active',
        ]);

        $schedule->update([
            'cut_off_time' => $validated['cut_off_time'] ? $validated['cut_off_time'].':00' : null,
            'opening_time' => $validated['opening_time'] ? $validated['opening_time'].':00' : null,
            'max_requests_per_day' => $validated['max_requests_per_day'] ?? null,
            'cut_off_enabled' => $request->boolean('cut_off_enabled', $oldValues['cut_off_enabled']),
            'is_active' => $request->boolean('is_active', $oldValues['is_active']),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'queue_schedule_updated',
            'subject_type' => QueueSchedule::class,
            'subject_id' => $schedule->id,
            'description' => 'Updated the daily queue schedule settings.',
            'old_values' => $oldValues,
            'new_values' => $schedule->only([
                'cut_off_time',
                'opening_time',
                'max_requests_per_day',
                'cut_off_enabled',
                'is_active',
            ]),
        ]);

        return back()->with('success', 'Queue schedule updated successfully.');
    }

    public function storeHoliday(Request $request, $id)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $schedule = QueueSchedule::findOrFail($id);

        $schedule->holidays()->updateOrCreate(
            ['holiday_date' => $validated['holiday_date']],
            ['name' => $validated['name'], 'is_active' => true]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'queue_schedule_holiday_added',
            'subject_type' => QueueSchedule::class,
            'subject_id' => $schedule->id,
            'description' => 'Added non-working date '.$validated['holiday_date'].' ('.$validated['name'].').',
            'new_values' => $validated,
        ]);

        return back()->with('success', 'Non-working date added.');
    }

    public function destroyHoliday(Request $request, $id, $holidayId)
    {
        $schedule = QueueSchedule::findOrFail($id);
        $holiday = $schedule->holidays()->findOrFail($holidayId);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'queue_schedule_holiday_removed',
            'subject_type' => QueueSchedule::class,
            'subject_id' => $schedule->id,
            'description' => 'Removed non-working date '.$holiday->holiday_date->toDateString().' ('.$holiday->name.').',
            'old_values' => [
                'holiday_date' => $holiday->holiday_date->toDateString(),
                'name' => $holiday->name,
            ],
        ]);

        $holiday->delete();

        return back()->with('success', 'Non-working date removed.');
    }
}