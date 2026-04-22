<?php

/**
 * KONFIGURASI MIDTRANS PAYMENT GATEWAY
 * 
 * Untuk Sandbox (testing):
 * - Mode: sandbox (false)
 * - Server Key & Client Key dari Midtrans sandbox account
 * 
 * Untuk Production (live):
 * - Mode: production (true)
 * - Server Key & Client Key dari Midtrans production account
 */

return [
    // Mode: true = production, false = sandbox
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    
    // Server Key untuk backend (simpan di .env - JANGAN BAGIKAN)
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-_nHp-Z6OfxD7sGsQZn9WRcJ'),
    
    // Client Key untuk frontend Snap.js (boleh public)
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-EkqXc0hMJdsJ3Z8M'),
    
    // Merchant ID
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    
    // Snap JS URLs
    'snap_js_url_sandbox' => 'https://app.sandbox.midtrans.com/snap/snap.js',
    'snap_js_url_production' => 'https://app.midtrans.com/snap/snap.js',
    
    // API URLs
    'api_url_sandbox' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
    'api_url_production' => 'https://app.midtrans.com/snap/v1/transactions',
    
    // Notification callback URL
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', '/payment/callback'),
    
    // Finish URL (redirect setelah user close popup)
    'finish_url' => env('MIDTRANS_FINISH_URL', '/payment/finish'),
];

