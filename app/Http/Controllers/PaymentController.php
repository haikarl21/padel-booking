<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createTransaction(Request $request)
    {
        try {
            $booking = Booking::findOrFail($request->booking_id);

            // Set Midtrans config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            // Create order
            $order_id = 'ORDER-' . strtoupper(Str::random(10)) . '-' . time();

            // Payment param  
            $transaction_details = array(
                'order_id' => $order_id,
                'gross_amount' => (int)$booking->total_price,
            );

            $customer_details = array(
                'first_name' => $booking->customer_name,
                'email' => $booking->email ?? 'guest@example.com',
                'phone' => $booking->phone,
            );

            $transaction = array(
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'payment_type' => 'snap',
                'snap' => array(
                    'snap_redirect' => 'false'
                )
            );

            // Get snap token
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);

            // Save to database
            Payment::create([
                'booking_id' => $booking->id,
                'order_id' => $order_id,
                'amount' => $booking->total_price,
                'status' => 'pending',
                'payment_type' => 'full',
                'snap_token' => $snapToken,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            $payment = Payment::where('order_id', $request->order_id)->first();

            if ($payment) {
                if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                    $payment->update(['status' => 'success']);
                    $payment->booking->update(['status' => 'approved']);
                } else if ($request->transaction_status == 'pending') {
                    $payment->update(['status' => 'pending']);
                } else if ($request->transaction_status == 'deny' || $request->transaction_status == 'cancel' || $request->transaction_status == 'expire') {
                    $payment->update(['status' => 'failed']);
                }
            }
        }

        return response('OK', 200);
    }

    public function checkStatusFromMidtrans($order_id)
    {
        try {
            // Set Midtrans config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            // Get status from Midtrans API
            $status = \Midtrans\Transaction::status($order_id);

            // Map Midtrans status to local status
            $transaction_status = $status->transaction_status ?? 'unknown';
            
            if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
                $local_status = 'success';
                $message = 'Pembayaran Berhasil';
            } else if ($transaction_status == 'pending') {
                $local_status = 'pending';
                $message = 'Menunggu Pembayaran';
            } else if ($transaction_status == 'expire' || $transaction_status == 'cancel' || $transaction_status == 'deny') {
                $local_status = 'failed';
                $message = 'Pembayaran Gagal';
            } else {
                $local_status = 'unknown';
                $message = 'Status Tidak Diketahui';
            }

            // Update database jika ada perubahan status
            $payment = Payment::where('order_id', $order_id)->first();
            if ($payment && $payment->status != $local_status) {
                $payment->update(['status' => $local_status]);
                
                // Update booking jika pembayaran berhasil
                if ($local_status == 'success') {
                    $payment->booking->update(['status' => 'approved']);
                }
            }

            return response()->json([
                'success' => true,
                'order_id' => $order_id,
                'transaction_status' => $transaction_status,
                'status' => $local_status,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal cek status: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Tampilkan halaman pilih metode pembayaran
     * 
     * @param Booking $booking
     * @return \Illuminate\View\View
     */
    public function selectMethod(Booking $booking)
    {
        // Check apakah booking sudah paid
        if ($booking->status === 'approved') {
            return redirect()->route('booking.receipt', $booking)
                ->with('info', 'Booking ini sudah approved. Lihat receipt Anda.');
        }

        return view('booking.select-payment-method', [
            'booking' => $booking,
        ]);
    }

    /**
     * Store selected payment method dan generate payment record
     * 
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMethod(Request $request, Booking $booking)
    {
        try {
            // Validasi method selection
            $request->validate([
                'payment_method' => 'required|in:bank_transfer,qrcode_dynamic',
            ]);

            $method = $request->input('payment_method');

            // Support bank_transfer dan qrcode_dynamic
            if (!in_array($method, ['bank_transfer', 'qrcode_dynamic'])) {
                return redirect()->back()
                    ->with('error', 'Metode pembayaran tidak valid.');
            }

            // Check apakah sudah ada payment pending/valid
            $existingPayment = $booking->payments()
                ->whereIn('status', ['pending', 'paid'])
                ->latest()
                ->first();

            if ($existingPayment) {
                // Redirect ke payment detail jika sudah ada
                return redirect()->route('payment.show', $booking);
            }

            return redirect()->route('payment.show', $booking);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan halaman detail pembayaran dengan bank account info
     * 
     * @param Booking $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Booking $booking)
    {
        // Check apakah sudah ada payment untuk booking ini
        $payment = $booking->payments()->latest()->first();

        // Jika belum ada payment, redirect ke method selection
        if (!$payment) {
            return redirect()->route('payment.select-method', $booking);
        }

        return view('booking.payment-detail', [
            'booking' => $booking,
            'payment' => $payment,
        ]);
    }

    /**
     * Get payment status via AJAX
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus(Payment $payment)
    {
        return response()->json([
            'status' => $payment->status,
        ]);
    }

    /**
     * Approve payment (admin action)
     * 
     * @param Request $request
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, Payment $payment)
    {
        try {
            $payment->update(['status' => 'success']);
            $payment->booking->update(['status' => 'approved']);

            return redirect()->back()
                ->with('success', 'Pembayaran berhasil di-approve.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat approve pembayaran.');
        }
    }

    /**
     * Reject payment (admin action)
     * 
     * @param Request $request
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Payment $payment)
    {
        try {
            $request->validate([
                'reason' => 'required|string|min:10|max:500',
            ]);

            $payment->update(['status' => 'failed']);

            return redirect()->back()
                ->with('success', 'Pembayaran berhasil di-reject.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat reject pembayaran.');
        }
    }

    /**
     * List payments untuk admin 
     * 
     * @return \Illuminate\View\View
     */
    public function listPayments()
    {
        try {
            $pendingPayments = Payment::where('status', 'pending')
                ->with('booking', 'booking.court')
                ->orderBy('created_at', 'desc')
                ->get();

            $allPayments = Payment::with('booking', 'booking.court')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.payments.index', [
                'pendingPayments' => $pendingPayments,
                'allPayments' => $allPayments,
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memuat data pembayaran.');
        }
    }
}


