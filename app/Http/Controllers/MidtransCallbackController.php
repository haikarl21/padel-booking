<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * MidtransCallbackController
 * 
 * Controller untuk handle callback/webhook dari Midtrans
 * Setiap kali ada perubahan status payment, Midtrans akan mengirim notifikasi ke endpoint ini
 * 
 * SECURITY NOTES:
 * - Endpoint ini HARUS TIDAK butuh authentication (public)
 * - Tetapi HARUS verify signature key untuk memastikan request dari Midtrans
 * - Selalu gunakan verifySignature method
 * - Log semua callback untuk auditing
 */
class MidtransCallbackController extends Controller
{
    protected $midtrans;

    /**
     * Constructor
     */
    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Handle Midtrans Callback/Webhook
     * 
     * Endpoint ini dipanggil oleh Midtrans setiap kali ada update status payment
     * 
     * Status yang mungkin diterima dari Midtrans:
     * - settlement: pembayaran berhasil
     * - pending: menunggu pembayaran
     * - expire: waktu pembayaran habis
     * - failed: pembayaran gagal
     * - cancel: pembayaran dibatalkan
     * - deny: pembayaran ditolak
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // Ambil semua data dari Midtrans
        // Midtrans mengirimkan data via POST request body
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // PENTING: Log semua callback untuk debugging dan auditing
        Log::info('Midtrans Callback Received', [
            'order_id'     => $orderId,
            'status_code'  => $statusCode,
            'gross_amount' => $grossAmount,
            'timestamp'    => now(),
        ]);

        /**
         * STEP 1: Verifikasi Signature
         * Pastikan request benar-benar dari Midtrans, bukan dari pihak lain
         * Midtrans mengirimkan signature_key yang harus kami verify
         */
        if (!$this->midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Invalid Midtrans Signature', [
                'order_id' => $orderId,
                'provided_signature' => $signatureKey,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        /**
         * STEP 2: Cari Payment Record
         * Cari payment berdasarkan order_id
         */
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            Log::error('Payment not found for order', ['order_id' => $orderId]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        /**
         * STEP 3: Parse Status dan Tentukan Action
         * Parse transaction status dari Midtrans response
         */
        $transactionStatus = $this->midtrans->parseTransactionStatus($request);

        Log::info('Payment Status Parsed', [
            'order_id' => $orderId,
            'old_status' => $payment->status,
            'new_status' => $transactionStatus,
        ]);

        /**
         * STEP 4: Update Payment Status
         * Update status pembayaran berdasarkan response dari Midtrans
         */
        switch ($transactionStatus) {
            case 'settlement':
                // Pembayaran BERHASIL
                $this->handleSuccessfulPayment($payment, $request);
                break;

            case 'pending':
                // Pembayaran masih PENDING
                // User sudah membuat transaksi tapi belum melakukan pembayaran
                // untuk bank transfer: user perlu transfer ke norek yang ditampilkan
                // untuk e-wallet: user perlu confirm di aplikasi e-wallet
                $payment->update([
                    'status' => 'pending',
                ]);
                Log::info('Payment still pending', ['order_id' => $orderId]);
                break;

            case 'expired':
                // Waktu pembayaran HABIS
                // User tidak membayar dalam waktu yang ditentukan
                $this->handleExpiredPayment($payment);
                break;

            case 'failed':
                // Pembayaran GAGAL
                // Bisa karena saldo kurang, pin salah, atau alasan lain
                $this->handleFailedPayment($payment);
                break;

            default:
                Log::warning('Unknown transaction status', [
                    'order_id' => $orderId,
                    'status' => $transactionStatus,
                ]);
        }

        /**
         * STEP 5: Log Response dari Midtrans
         * Simpan full response dari Midtrans untuk auditing
         */
        $payment->update([
            'midtrans_response' => json_encode($request->all()),
        ]);

        /**
         * STEP 6: Response ke Midtrans
         * Balas dengan status 200 OK agar Midtrans tahu callback sudah diterima
         * Jika tidak response 200, Midtrans akan retry callback beberapa kali
         */
        return response()->json([
            'success' => true,
            'message' => 'Callback processed',
        ], 200);
    }

    /**
     * Handle Pembayaran BERHASIL
     * 
     * Ketika payment berhasil:
     * 1. Update status payment menjadi 'settlement'
     * 2. Update status booking menjadi 'approved'
     * 3. Update paid amount di booking
     * 4. Catat waktu pembayaran
     * 
     * @param Payment $payment
     * @param Request $request
     */
    private function handleSuccessfulPayment(Payment $payment, Request $request)
    {
        // Update payment
        $payment->update([
            'status' => 'settlement',
            'transaction_id' => $request->input('transaction_id'),
            'paid_at' => now(),
        ]);

        // Ambil booking yang terkait
        $booking = $payment->booking;

        // Update status booking ke 'approved' karena pembayaran sudah lengkap
        // atau ke 'partial' jika masih ada sisa pembayaran
        $newPaid = $booking->paid + $payment->amount;
        $booking->update([
            'paid'      => $newPaid,
            'remaining' => max(0, $booking->total_price - $newPaid),
            'status'    => ($newPaid >= $booking->total_price) ? 'approved' : 'partial',
        ]);

        Log::info('Payment successful', [
            'order_id' => $payment->order_id,
            'booking_id' => $booking->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Handle Pembayaran EXPIRED
     * 
     * Ketika waktu pembayaran habis:
     * 1. Update payment status ke 'expired'
     * 2. Tidak update booking status (transaksi gagal)
     * 3. User bisa membuat transaksi baru
     * 
     * @param Payment $payment
     */
    private function handleExpiredPayment(Payment $payment)
    {
        $payment->update([
            'status' => 'expired',
        ]);

        Log::warning('Payment expired', [
            'order_id' => $payment->order_id,
            'booking_id' => $payment->booking_id,
        ]);
    }

    /**
     * Handle Pembayaran GAGAL
     * 
     * Ketika pembayaran gagal:
     * 1. Update payment status ke 'failed'
     * 2. Tidak update booking status
     * 3. User bisa coba ulang pembayaran
     * 
     * @param Payment $payment
     */
    private function handleFailedPayment(Payment $payment)
    {
        $payment->update([
            'status' => 'failed',
        ]);

        Log::warning('Payment failed', [
            'order_id' => $payment->order_id,
            'booking_id' => $payment->booking_id,
        ]);
    }
}
