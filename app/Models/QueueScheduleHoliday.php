<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueScheduleHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_schedule_id',
        'holiday_date',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(QueueSchedule::class, 'queue_schedule_id');
    }
}