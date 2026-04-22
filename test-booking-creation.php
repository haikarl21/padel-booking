<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use Illuminate\Support\Str;

try {
    $court = Court::first();
    if (!$court) {
        echo "❌ No courts found\n";
        exit(1);
    }

    $timeSlots = TimeSlot::limit(3)->pluck('id')->toArray();
    if (empty($timeSlots)) {
        echo "❌ No time slots found\n";
        exit(1);
    }

    $booking = Booking::create([
        'booking_code' => 'BKG-TEST-' . strtoupper(Str::random(4)),
        'court_id' => $court->id,
        'user_id' => 1,
        'time_slot_id' => $timeSlots[0],
        'date' => '2026-04-20',
        'duration_hours' => 3,
        'start_time' => '09:00',
        'customer_name' => 'Test User',
        'phone' => '08123456789',
        'email' => 'test@example.com',
        'total_price' => 300000,
        'paid' => 0,
        'remaining' => 300000,
        'status' => 'pending',
    ]);

    echo "✓ Booking created successfully!\n";
    echo "Booking ID: " . $booking->id . "\n";
    echo "Booking Code: " . $booking->booking_code . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
