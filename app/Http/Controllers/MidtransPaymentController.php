<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MidtransPaymentController extends Controller
{
    /**
     * Get Snap Token untuk Midtrans Snap
     * Endpoint ini dipanggil via AJAX dari browser untuk mendapatkan token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSnapToken(Request $request)
    {
        try {
            // Validasi
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
            ]);

            $booking = Booking::findOrFail($request->booking_id);

            // Guest bookings allowed - tidak perlu login untuk bayar

            // Cek apakah booking sudah paid
            // Gunakan closure untuk grouping OR logic dengan benar
            $existing_payment = $booking->payments()
                ->where(function($query) {
                    $query->where('transaction_status', 'capture')
                          ->orWhere('transaction_status', 'settlement');
                })
                ->latest()
                ->first();

            if ($existing_payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking sudah dibayar'
                ], 400);
            }

            // Set Midtrans configuration
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Buat atau update payment record
            $payment = $booking->payments()->first() ?? new Payment();
            
            if (!$payment->exists) {
                $payment->booking_id = $booking->id;
            }

            // Generate or get order ID
            $order_id = $payment->order_id ?? 'ORDER-' . $booking->id . '-' . uniqid();

            // Parameter transaksi
            $transaction_details = [
                'order_id' => $order_id,
                'gross_amount' => (int) $booking->total_price,
            ];

            $item_details = [
                [
                    'id' => 'court-' . $booking->court_id,
                    'price' => (int) $booking->total_price,
                    'quantity' => 1,
                    'name' => $booking->court->name . ' - ' . $booking->date->format('d/m/Y') . ' ' . $booking->timeSlot->start_time,
                ]
            ];

            /**
             * CUSTOMER DETAILS - Ambil dari booking, jangan dari Auth
             * Ini penting karena sistem tidak punya login (guest checkout)
             */
            $customer_details = [
                'first_name' => $booking->customer_name ?? 'Guest',
                'email' => $booking->email ?? 'guest@mail.com',  // Default jika null
                'phone' => $booking->phone ?? '',
            ];

            // Snap body
            $snap_body = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details,
                'callbacks' => [
                    'finish' => route('payment.finish'),
                ],
            ];

            // Generate snap token
            $snap_token = \Midtrans\Snap::getSnapToken($snap_body);

            // Save payment record
            $payment->order_id = $order_id;
            $payment->amount = $booking->total_price;
            $payment->gross_amount = $booking->total_price;
            $payment->payment_type = 'full';
            $payment->payment_method = 'midtrans_snap';
            $payment->status = 'pending';                      // ← TAMBAHAN: Local payment status
            $payment->transaction_status = 'pending';         // Midtrans transaction status
            $payment->snap_token = $snap_token;
            $payment->save();

            Log::info('Snap token generated', [
                'booking_id' => $booking->id,
                'order_id' => $order_id,
                'amount' => $booking->total_price,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snap_token,
                'payment_id' => $payment->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating snap token', [
                'error' => $e->getMessage(),
                'booking_id' => $request->booking_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat snap token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Setelah user close popup Snap, redirect ke finish URL
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function finish(Request $request)
    {
        $order_id = $request->order_id;

        // Cari payment berdasarkan order_id
        $payment = Payment::where('order_id', $order_id)->firstOrFail();
        $booking = $payment->booking;

        return view('payment.finish', [
            'payment' => $payment,
            'booking' => $booking,
            'order_id' => $order_id,
        ]);
    }

    /**
     * Callback dari Midtrans untuk payment notification
     * Endpoint ini dipanggil oleh Midtrans server oleh Midtrans saat ada update transaksi
     * HARUS PUBLIC (tanpa authentication)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        try {
            // Verify signature dari Midtrans
            $server_key = config('midtrans.server_key');
            $order_id = $request->order_id;
            $status_code = $request->status_code;
            $gross_amount = $request->gross_amount;
            $server_signature = $request->signature_key;

            // Generate signature untuk verify
            $my_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

            // Verify signature
            if ($server_signature !== $my_signature) {
                Log::warning('Invalid signature from Midtrans', [
                    'order_id' => $order_id,
                    'received_signature' => $server_signature,
                    'expected_signature' => $my_signature,
                ]);
                return response('Invalid signature', 403);
            }

            // Cari payment
            $payment = Payment::where('order_id', $order_id)->first();
            if (!$payment) {
                Log::warning('Payment not found', ['order_id' => $order_id]);
                return response('Payment not found', 404);
            }

            // Tentukan status transaksi berdasarkan transaction_status dari Midtrans
            $transaction_status = $request->transaction_status;
            $fraud_status = $request->fraud_status;

            $status_mapping = [
                'capture' => [
                    'fraud_status' => 'accept',
                    'payment_status' => 'paid',
                ],
                'settlement' => 'paid',
                'pending' => 'pending',
                'deny' => 'rejected',
                'cancel' => 'rejected',
                'expire' => 'expired',
            ];

            // Update payment record
            $payment->transaction_status = $transaction_status;
            $payment->fraud_status = $fraud_status;

            if ($transaction_status === 'capture') {
                if ($fraud_status === 'accept') {
                    $payment->status = 'paid';
                    $payment->paid_at = now();
                    
                    // Update booking status
                    $payment->booking->update(['status' => 'approved']);
                }
            } elseif ($transaction_status === 'settlement') {
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->booking->update(['status' => 'approved']);
            } elseif ($transaction_status === 'deny' || $transaction_status === 'cancel') {
                $payment->status = 'rejected';
                $payment->rejection_reason = 'Pembayaran ditolak atau dibatalkan oleh sistem';
            } elseif ($transaction_status === 'expire') {
                $payment->status = 'expired';
            }

            $payment->save();

            Log::info('Payment callback processed', [
                'order_id' => $order_id,
                'transaction_status' => $transaction_status,
                'fraud_status' => $fraud_status,
                'payment_status' => $payment->status,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error processing Midtrans callback', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response('Error', 500);
        }
    }

    /**
     * Check status pembayaran (untuk polling dari frontend)
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Payment $payment)
    {
        // Allow guest to check payment status
        // Verify signature dari Midtrans untuk double check
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        try {
            $status = \Midtrans\Transaction::status($payment->order_id);
            
            // Cast as object for safe access
            $statusData = (object)$status;
            
            return response()->json([
                'success' => true,
                'transaction_status' => $statusData->transaction_status ?? null,
                'payment_status' => $payment->status,
                'order_id' => $payment->order_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test Receipt - Menampilkan receipt pembayaran untuk testing
     * Berguna untuk memtest UI tanpa perlu pembayaran actual
     * 
     * @param Payment $payment
     * @return \Illuminate\View\View
     */
    public function testReceipt(Payment $payment)
    {
        $booking = $payment->booking;
        return view('payment.test-receipt', [
            'payment' => $payment,
            'booking' => $booking,
            'order_id' => $payment->order_id,
        ]);
    }

    /**
     * Mark As Paid - Menandai pembayaran sebagai paid untuk testing
     * ONLY FOR TESTING - Dalam production, jangan expose endpoint ini
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(Payment $payment)
    {
        // SECURITY: Tambahkan cek jika tidak dalam development
        if (config('app.env') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 403);
        }

        try {
            // Update payment status
            $payment->update([
                'status' => 'paid',
                'transaction_status' => 'settlement',
                'paid_at' => now(),
            ]);

            // Update booking status
            $payment->booking->update([
                'status' => 'approved',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as paid',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
