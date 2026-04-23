<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createTransaction(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'payment_type' => 'required|in:full,partial',
            ]);

            $booking = Booking::findOrFail($request->booking_id);

            // Block new transactions if already fully approved
            if ($booking->status === 'approved' || (float) $booking->remaining <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking sudah dibayar.',
                ], 400);
            }

            $paymentType = $request->input('payment_type');

            // Partial payment only allowed as first payment (50% of total)
            if ($paymentType === 'partial' && (float) $booking->paid > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran 50% hanya bisa dilakukan sebagai pembayaran pertama. Silakan pilih pembayaran penuh untuk melunasi sisa.',
                ], 400);
            }

            $amountToPay = $paymentType === 'partial'
                ? ((float) $booking->total_price * 0.5)
                : (float) $booking->remaining;

            // Ensure positive amount
            $amountToPay = max(0, $amountToPay);

            if ($amountToPay <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tagihan yang perlu dibayar.',
                ], 400);
            }

            // Set Midtrans config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Create order
            $order_id = 'ORDER-' . strtoupper(Str::random(10)) . '-' . time();

            // Payment param  
            $transaction_details = array(
                'order_id' => $order_id,
                'gross_amount' => (int) round($amountToPay),
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
            $payment = new Payment();
            $payment->booking_id = $booking->id;
            $payment->order_id = $order_id;
            $payment->amount = $amountToPay;
            $payment->gross_amount = $amountToPay;
            $payment->status = 'pending';
            $payment->payment_type = $paymentType;
            $payment->payment_method = 'midtrans_snap';
            $payment->snap_token = $snapToken;
            $payment->save();

            Log::info('Midtrans transaction created', [
                'booking_id' => $booking->id,
                'order_id' => $order_id,
                'payment_type' => $paymentType,
                'amount' => $amountToPay,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $order_id,
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
                $txStatus = $request->transaction_status;

                if ($txStatus === 'settlement' || $txStatus === 'capture') {
                    // Idempotent: only set paid_at once
                    if ($payment->status !== 'settlement') {
                        $payment->status = 'settlement';
                        $payment->transaction_id = $request->transaction_id;
                        $payment->midtrans_signature_key = $request->signature_key;
                        $payment->midtrans_response = json_encode($request->all());
                        $payment->paid_at = $payment->paid_at ?? now();
                        $payment->save();
                    }

                    $booking = $payment->booking;

                    // Recalculate from successful payments to avoid double counting
                    $paidTotal = (float) $booking->payments()
                        ->whereIn('status', ['settlement', 'capture'])
                        ->sum('amount');

                    $remaining = max(0, (float) $booking->total_price - $paidTotal);
                    $booking->paid = $paidTotal;
                    $booking->remaining = $remaining;
                    $booking->status = $remaining <= 0 ? 'approved' : 'partial';
                    $booking->save();

                } elseif ($txStatus === 'pending') {
                    $payment->update([
                        'status' => 'pending',
                        'midtrans_signature_key' => $request->signature_key,
                        'midtrans_response' => json_encode($request->all()),
                    ]);
                } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
                    $payment->update([
                        'status' => $txStatus === 'expire' ? 'expired' : 'failed',
                        'midtrans_signature_key' => $request->signature_key,
                        'midtrans_response' => json_encode($request->all()),
                    ]);
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

            if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
                $local_status = 'settlement';
                $message = 'Pembayaran Berhasil';
            } elseif ($transaction_status === 'pending') {
                $local_status = 'pending';
                $message = 'Menunggu Pembayaran';
            } elseif ($transaction_status === 'expire') {
                $local_status = 'expired';
                $message = 'Pembayaran Kedaluwarsa';
            } elseif (in_array($transaction_status, ['cancel', 'deny'])) {
                $local_status = 'failed';
                $message = 'Pembayaran Gagal';
            } else {
                $local_status = 'unknown';
                $message = 'Status Tidak Diketahui';
            }

            // Update database jika ada perubahan status
            $payment = Payment::where('order_id', $order_id)->first();
            if ($payment && $payment->status !== $local_status) {
                $payment->status = $local_status;
                $payment->transaction_id = $status->transaction_id ?? $payment->transaction_id;
                $payment->midtrans_response = json_encode($status);
                if ($local_status === 'settlement' && !$payment->paid_at) {
                    $payment->paid_at = now();
                }
                $payment->save();
            }

            // Update booking totals/status when paid
            if ($payment && in_array($local_status, ['settlement', 'capture'], true)) {
                $booking = $payment->booking;
                if ($booking) {
                    $paidTotal = (float) $booking->payments()
                        ->whereIn('status', ['settlement', 'capture'])
                        ->sum('amount');
                    $remaining = max(0, (float) $booking->total_price - $paidTotal);
                    $booking->paid = $paidTotal;
                    $booking->remaining = $remaining;
                    $booking->status = $remaining <= 0 ? 'approved' : 'partial';
                    $booking->save();
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

    /**
     * Legacy payment endpoint (used by older UI/tests).
     * - Validates optional proof_file
     * - For payment_method=qris: mark as completed immediately (non-Midtrans).
     */
    public function process(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:full,partial',
            'payment_method' => 'required|string|max:50',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $paymentType = $validated['payment_type'];
        $paymentMethod = strtolower($validated['payment_method']);

        // Compute amount: partial = 50% of total (first payment), full = remaining
        $amountToPay = $paymentType === 'partial'
            ? ((float) $booking->total_price * 0.5)
            : (float) $booking->remaining;
        $amountToPay = max(0, $amountToPay);

        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->order_id = $payment->order_id ?? ('LEGACY-' . strtoupper(Str::random(10)) . '-' . time());
        $payment->payment_type = $paymentType;
        $payment->payment_method = $paymentMethod;
        $payment->amount = $amountToPay;
        $payment->gross_amount = $amountToPay;

        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('payment-proofs', 'public');
            $payment->proof_file_path = $path;
        }

        // For legacy QRIS we mark as completed immediately (for testing/backward compatibility)
        if ($paymentMethod === 'qris') {
            $payment->status = 'completed';
            $payment->paid_at = now();
        } else {
            // Other methods remain pending in legacy flow
            $payment->status = 'pending';
        }

        $payment->save();

        // Update booking totals/status if completed
        if ($payment->status === 'completed') {
            $paidTotal = (float) $booking->payments()
                ->whereIn('status', ['completed', 'settlement', 'capture'])
                ->sum('amount');

            $remaining = max(0, (float) $booking->total_price - $paidTotal);
            $booking->paid = $paidTotal;
            $booking->remaining = $remaining;
            $booking->status = $remaining <= 0 ? 'approved' : 'partial';
            $booking->save();
        }

        return redirect()->route('booking.detail', $booking)
            ->with('payment_success', 'Pembayaran berhasil diproses.');
    }
}


