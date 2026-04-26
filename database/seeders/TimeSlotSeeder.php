<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [];
        for ($hour = 9; $hour < 22; $hour++) {
            $startTime = sprintf('%02d:00', $hour);
            $endTime = sprintf('%02d:00', $hour + 1);
            $slots[] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'display_text' => "$startTime - $endTime"
            ];
        }

        foreach ($slots as $slot) {
            TimeSlot::firstOrCreate(
                [
                    'start_time' => $slot['start_time'], 
                    'end_time' => $slot['end_time']
                ],
                [
                    'display_text' => $slot['display_text']
                ]
            );
        }
    }
}
