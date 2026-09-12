<?php

namespace Database\Seeders;

use App\Models\QueueSchedule;
use Illuminate\Database\Seeder;

class QueueScheduleSeeder extends Seeder
{
    public function run(): void
    {
        QueueSchedule::updateOrCreate(
            ['id' => 1],
            [
                'cut_off_time' => '16:00:00',
                'opening_time' => '08:00:00',
                'max_requests_per_day' => null,
                'cut_off_enabled' => true,
                'is_active' => true,
                'timezone' => 'Asia/Manila',
            ]
        );
    }
}