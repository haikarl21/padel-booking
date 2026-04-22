<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        Court::updateOrCreate(
            ['slug' => 'lapangan-a'],
            [
                'name' => 'Lapangan A',
                'description' => 'Premium indoor padel court with professional lighting',
                'price_per_hour' => 100000,
                'status' => 'active',
            ]
        );

        Court::updateOrCreate(
            ['slug' => 'lapangan-b'],
            [
                'name' => 'Lapangan B',
                'description' => 'Indoor padel court with modern facilities',
                'price_per_hour' => 120000,
                'status' => 'active',
            ]
        );

        Court::updateOrCreate(
            ['slug' => 'lapangan-c'],
            [
                'name' => 'Lapangan C',
                'description' => 'Outdoor padel court with covered roof',
                'price_per_hour' => 80000,
                'status' => 'active',
            ]
        );
    }
}
