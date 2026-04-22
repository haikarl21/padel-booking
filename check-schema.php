<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('bookings');
echo "Bookings table columns:\n";
foreach ($columns as $col) {
    $type = Schema::getColumnType('bookings', $col);
    echo "  - $col ($type)\n";
}

// Check sample booking
echo "\nSample booking:\n";
$booking = DB::table('bookings')->where('booking_code', 'BKG-TEST-BXWT')->first();
if ($booking) {
    echo json_encode((array)$booking, JSON_PRETTY_PRINT);
}
