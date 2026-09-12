<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\QueueSchedule;
use Illuminate\Support\Carbon;

class QueueScheduleService
{
    public function getActiveSchedule(): ?QueueSchedule
    {
        return QueueSchedule::where('is_active', true)->orderBy('id')->first();
    }

    public function isWorkingDay(Carbon $date, ?QueueSchedule $schedule = null): bool
    {
        $schedule ??= $this->getActiveSchedule();

        if ($date->isWeekend()) {
            return false;
        }

        if ($schedule && $schedule->holidays()
            ->whereDate('holiday_date', $date->toDateString())
            ->where('is_active', true)
            ->exists()) {
            return false;
        }

        return true;
    }

    public function isAtOrAfterCutoff(Carbon $now, ?QueueSchedule $schedule = null): bool
    {
        $schedule ??= $this->getActiveSchedule();

        if (! $schedule || ! $schedule->cut_off_enabled || ! $schedule->cut_off_time) {
            return false;
        }

        $cutoff = Carbon::parse($schedule->cut_off_time, $schedule->timezone ?: 'Asia/Manila');

        return $now->gte($cutoff);
    }

    public function capacityRemaining(Carbon $date, ?QueueSchedule $schedule = null): int
    {
        $schedule ??= $this->getActiveSchedule();

        if (! $schedule || $schedule->max_requests_per_day === null) {
            return PHP_INT_MAX;
        }

        $count = DocumentRequest::whereDate('service_date', $date->toDateString())
            ->whereIn('status', ['pending', 'reviewing'])
            ->count();

        return (int) max(0, $schedule->max_requests_per_day - $count);
    }

    public function assignServiceDate(?QueueSchedule $schedule = null): array
    {
        $schedule ??= $this->getActiveSchedule();
        $now = now()->timezone($schedule?->timezone ?: 'Asia/Manila');

        if (! $schedule || ! $schedule->cut_off_enabled) {
            return [
                'service_date' => $now->toDateString(),
                'scheduled_after_cutoff' => false,
            ];
        }

        $today = $now->copy();
        if ($this->isWorkingDay($today, $schedule)
            && ! $this->isAtOrAfterCutoff($now, $schedule)
            && $this->capacityRemaining($today, $schedule) > 0) {
            return [
                'service_date' => $today->toDateString(),
                'scheduled_after_cutoff' => false,
            ];
        }

        $assignment = $this->nextWorkingDayWithCapacity($today, $schedule);

        if ($assignment) {
            return [
                'service_date' => $assignment->toDateString(),
                'scheduled_after_cutoff' => true,
            ];
        }

        $cursor = $today->copy()->addDay();
        while (! $this->isWorkingDay($cursor, $schedule)) {
            $cursor->addDay();
        }

        return [
            'service_date' => $cursor->toDateString(),
            'scheduled_after_cutoff' => true,
        ];
    }

    public function nextWorkingDayWithCapacity(Carbon $start, ?QueueSchedule $schedule = null): ?Carbon
    {
        $schedule ??= $this->getActiveSchedule();

        $cursor = $start->copy()->addDay();
        for ($i = 0; $i < 30; $i++) {
            if ($this->isWorkingDay($cursor, $schedule) && $this->capacityRemaining($cursor, $schedule) > 0) {
                return $cursor->copy();
            }
            $cursor->addDay();
        }

        return null;
    }

    public function queueStatus(?Carbon $now = null, ?QueueSchedule $schedule = null): array
    {
        $now ??= now()->timezone($schedule?->timezone ?: 'Asia/Manila');
        $schedule ??= $this->getActiveSchedule();

        $enabled = $schedule && $schedule->cut_off_enabled;

        $isOpen = true;
        if ($enabled) {
            $isOpen = $this->isWorkingDay($now, $schedule)
                && ! $this->isAtOrAfterCutoff($now, $schedule)
                && $this->capacityRemaining($now, $schedule) > 0;
        }

        return [
            'is_open' => $isOpen,
            'cut_off_enabled' => $enabled,
            'cut_off_time' => $schedule?->cut_off_time,
            'opening_time' => $schedule?->opening_time,
            'max_requests_per_day' => $schedule?->max_requests_per_day,
            'timezone' => $schedule?->timezone ?: 'Asia/Manila',
            'service_date' => $now->toDateString(),
        ];
    }
}