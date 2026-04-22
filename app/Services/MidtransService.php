<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * MidtransService
 * Service untuk handle semua interaksi dengan Midtrans Payment Gateway
 * 
 * Fitur:
 * - Membuat transaksi di Midtrans
 * - Generate Snap token untuk payment page
 * - Verifikasi payment status
 * - Handle webhook/callback dari Midtrans
 */
class MidtransService
{
    /**
     * Constructor - Setup Midtrans Configuration
     * Inisialisasi konfigurasi Midtrans dengan server key dan client key dari .env
     */
    public function __construct()
    {
        // Require Midtrans bootstrap file untuk memastikan semua classes ter-load
        require_once base_path('vendor/midtrans/midtrans-php/Midtrans.php');
        
        // Setup Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Buat Transaksi dan Generate Snap Token
     * 
     * @param int $bookingId - ID booking dari database
     * @param float $amount - Jumlah yang akan dibayar
     * @param string $paymentType - 'full' atau 'partial'
     * @param array $customerData - Data customer (name, email, phone)
     * 
     * @return array - Response dari Midtrans berisi snap_token
     * @throws Exception
     */
    public function createTransaction($bookingId, $amount, $paymentType, $customerData)
    {
        try {
            // Generate order ID unik untuk setiap transaksi
            // Format: BOOKING-{bookingId}-{timestamp}
            $orderId = 'BOOKING-' . $bookingId . '-' . time();
            
            // Siapkan data transaksi untuk Midtrans
            $transactionData = [
                'transaction_details' => [
                    'order_id'      => $orderId,
                    'gross_amount'  => (int) $amount, // Harus integer (dalam rupiah)
                ],
                'customer_details' => [
                    'first_name'    => $customerData['name'] ?? 'Customer',
                    'email'         => $customerData['email'] ?? '',
                    'phone'         => $customerData['phone'] ?? '',
                ],
                'item_details' => [
                    [
                        'id'       => 'BOOKING-' . $bookingId,
                        'price'    => (int) $amount,
                        'quantity' => 1,
                        'name'     => $paymentType === 'full' 
                            ? 'Pembayaran Penuh Booking Lapangan' 
                            : 'Pembayaran Awal (50%) Booking Lapangan'
                    ]
                ],
                // Explicit payment methods untuk Sandbox testing
                'enabled_payments' => [
                    'credit_card',    // Kartu kredit
                    'qris',           // QRIS
                    'bca',            // Bank BCA
                    'bni',            // Bank BNI
                    'bri',            // Bank BRI
                ],
            ];

            // Call Midtrans Snap API untuk get token
            $snapToken = Snap::getSnapToken($transactionData);

            return [
                'status'     => 'success',
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
                'response'   => $transactionData,
            ];
        } catch (Exception $e) {
            // Log error untuk debugging dengan detail
            Log::error('Midtrans API Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'server_key_set' => !empty(config('midtrans.server_key')),
                'client_key_set' => !empty(config('midtrans.client_key')),
            ]);
            
            return [
                'status'  => 'error',
                'message' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cek Status Transaksi di Midtrans
     * 
     * @param string $orderId - Order ID dari transaksi
     * 
     * @return array - Status dan detail transaksi dari Midtrans
     * @throws Exception
     */
    public function getTransactionStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            
            return [
                'status'       => 'success',
                'order_id'     => $orderId,
                'transactions' => $status,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Status Check Error: ' . $e->getMessage());
            
            return [
                'status'  => 'error',
                'message' => 'Gagal mengecek status transaksi',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifikasi Webhook Signature dari Midtrans
     * PENTING: Untuk keamanan, setiap callback dari Midtrans harus diverifikasi signature-nya
     * 
     * @param string $orderId - Order ID dari notifikasi
     * @param string $statusCode - Status code dari notifikasi
     * @param string $grossAmount - Gross amount dari notifikasi
     * @param string $signatureKey - Signature key yang dikirim Midtrans
     * 
     * @return bool - True jika signature valid, false jika invalid
     */
    public function verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)
    {
        try {
            // Buat signature dengan format: {orderId}{statusCode}{grossAmount}{serverKey}
            $serverKey = config('midtrans.server_key');
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            
            // Bandingkan signature yang diterima dengan signature yang kami hitung
            return hash_equals($expectedSignature, $signatureKey);
        } catch (Exception $e) {
            Log::error('Signature Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse response Midtrans dan tentukan status pembayaran
     * 
     * @param object $notification - Notification object dari Midtrans
     * 
     * @return string - Status: 'pending', 'settlement', 'expired', 'failed', atau 'unknown'
     */
    public function parseTransactionStatus($notification)
    {
        // Ambil transaction status dari Midtrans response
        $transactionStatus = $notification->transaction_status ?? null;
        $fraudStatus = $notification->fraud_status ?? null;

        // Tentukan status berdasarkan response Midtrans
        // settlement = pembayaran berhasil
        if ($transactionStatus == 'settlement') {
            return 'settlement';
        }
        // pending = menunggu pembayaran
        elseif ($transactionStatus == 'pending') {
            return 'pending';
        }
        // expired = transaksi hangus / kadaluarsa
        elseif ($transactionStatus == 'expire') {
            return 'expired';
        }
        // failed atau cancel = transaksi gagal/dibatalkan
        elseif (in_array($transactionStatus, ['failed', 'cancel', 'deny'])) {
            return 'failed';
        }
        
        return 'unknown';
    }

    /**
     * Get Client Key (untuk frontend)
     * Client key diperlukan untuk Snap Payment Page integration
     * 
     * @return string - Client key untuk Midtrans
     */
    public function getClientKey()
    {
        return config('midtrans.client_key');
    }
}
