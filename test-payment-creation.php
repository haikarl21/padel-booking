<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;

try {
    $booking = Booking::where('booking_code', 'BKG-TEST-BXWT')->first();
    
    if (!$booking) {
        echo "❌ Booking not found\n";
        exit(1);
    }

    // Create payment record with 'success' status
    $payment = Payment::create([
        'booking_id' => $booking->id,
        'order_id' => 'ORDER-TEST-' . strtoupper(Str::random(10)),
        'amount' => $booking->total_price,
        'status' => 'success',
        'payment_type' => 'full',
        'snap_token' => 'test-snap-token-' . Str::random(20),
        'payment_method' => 'qris',
    ]);

    // Update booking status to approved
    $booking->update(['status' => 'approved']);

    echo "✓ Payment created successfully!\n";
    echo "Payment ID: " . $payment->id . "\n";
    echo "Payment Status: " . $payment->status . "\n";
    echo "Booking Status: " . $booking->status . "\n";
    echo "\n✓ Barcode should now be visible in the detail page!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
