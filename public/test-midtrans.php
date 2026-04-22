<?php
// Test file to verify Midtrans can be loaded

try {
    require_once __DIR__ . '/../vendor/midtrans/midtrans-php/Midtrans.php';
    
    // If we reach here, Midtrans loaded successfully
    echo "✓ Midtrans Midtrans.php loaded successfully\n";
    
    // Try to access Config class
    if (class_exists('Midtrans\Config')) {
        echo "✓ Midtrans\Config class found\n";
    } else {
        echo "✗ Midtrans\Config class NOT found\n";
    }
    
    // Try to access Snap class
    if (class_exists('Midtrans\Snap')) {
        echo "✓ Midtrans\Snap class found\n";
    } else {
        echo "✗ Midtrans\Snap class NOT found\n";
    }
    
    // Try to access Transaction class
    if (class_exists('Midtrans\Transaction')) {
        echo "✓ Midtrans\Transaction class found\n";
    } else {
        echo "✗ Midtrans\Transaction class NOT found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error loading Midtrans:\n";
    echo $e->getMessage() . "\n";
}
