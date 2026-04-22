<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            ['start_time' => '09:00', 'end_time' => '10:00', 'display_text' => '09:00 - 10:00'],
            ['start_time' => '11:00', 'end_time' => '12:00', 'display_text' => '11:00 - 12:00'],
            ['start_time' => '13:00', 'end_time' => '14:00', 'display_text' => '13:00 - 14:00'],
            ['start_time' => '15:00', 'end_time' => '16:00', 'display_text' => '15:00 - 16:00'],
            ['start_time' => '17:00', 'end_time' => '18:00', 'display_text' => '17:00 - 18:00'],
            ['start_time' => '19:00', 'end_time' => '20:00', 'display_text' => '19:00 - 20:00'],
            ['start_time' => '20:00', 'end_time' => '21:00', 'display_text' => '20:00 - 21:00'],
        ];

        foreach ($slots as $slot) {
            TimeSlot::create($slot);
        }
    }
}
