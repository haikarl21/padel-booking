<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use Illuminate\Support\Str;

echo "\n=== TEST: Multi-Slot Booking Tracking ===\n\n";

// Get 3 consecutive slots
$slots = TimeSlot::orderBy('start_time')->limit(3)->get();
$slotIds = $slots->pluck('id')->toArray();

echo "Selected slots:\n";
foreach ($slots as $slot) {
    echo "  - " . $slot->display_text . " (ID: " . $slot->id . ")\n";
}

$court = Court::first();

// Create booking with 3 slots
$booking = Booking::create([
    'booking_code' => 'BKG-MULTI-' . strtoupper(Str::random(4)),
    'court_id' => $court->id,
    'user_id' => 1,
    'time_slot_id' => $slotIds[0],
    'time_slot_ids' => $slotIds, // Multiple slot IDs
    'date' => '2026-04-21',
    'duration_hours' => 3,
    'start_time' => $slots->first()->start_time,
    'customer_name' => 'Test Multi Slot',
    'phone' => '08123456789',
    'email' => 'test@example.com',
    'total_price' => 300000,
    'paid' => 0,
    'remaining' => 300000,
    'status' => 'pending',
]);

echo "\nBooking created:\n";
echo "  Code: " . $booking->booking_code . "\n";
echo "  Duration: " . $booking->duration_hours . " hours\n";
echo "  time_slot_ids JSON: " . json_encode($booking->time_slot_ids) . "\n";

// Verify the booked slots
echo "\nVerify booked slots for " . $booking->date->format('Y-m-d') . ":\n";
$bookings = Booking::where('court_id', $court->id)
    ->whereDate('date', $booking->date)
    ->get(['time_slot_ids', 'duration_hours']);

$bookedSlots = [];
foreach ($bookings as $b) {
    if ($b->time_slot_ids && is_array($b->time_slot_ids)) {
        $bookedSlots = array_merge($bookedSlots, $b->time_slot_ids);
    }
}
$bookedSlots = array_unique($bookedSlots);

echo "  ✓ Total booked slots: " . count($bookedSlots) . "\n";
foreach ($bookedSlots as $slotId) {
    $slot = TimeSlot::find($slotId);
    echo "    - Slot ID $slotId: " . $slot->display_text . "\n";
}

echo "\n✓ Test completed successfully!\n\n";
