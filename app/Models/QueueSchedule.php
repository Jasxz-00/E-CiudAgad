<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'cut_off_time',
        'opening_time',
        'max_requests_per_day',
        'cut_off_enabled',
        'is_active',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'max_requests_per_day' => 'integer',
            'cut_off_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(QueueScheduleHoliday::class);
    }
}