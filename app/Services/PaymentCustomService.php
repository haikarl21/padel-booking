<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * PaymentCustomService
 * Service untuk handle custom payment system tanpa payment gateway
 * 
 * Features:
 * - Generate 3-digit unique code untuk setiap transaksi
 * - Calculate total pembayaran = amount + unique code
 * - Set expiration time 30 menit
 * - Validate file upload (jpg/png, max 5MB)
 * - Check payment expiration status
 * - Security: sanitize input, basic validation
 */
class PaymentCustomService
{
    /**
     * Bank account information untuk tampilan di user
     * Struktur sederhana - bisa di-extend ke database jika perlu
     */
    const BANK_ACCOUNT = [
        'bank_name' => 'Bank Central Asia',
        'account_number' => '1234567890',
        'account_holder' => 'PT. Padel Booking',
    ];

    /**
     * Konfigurasi payment
     */
    const UNIQUE_CODE_MIN = 100;
    const UNIQUE_CODE_MAX = 999;
    const EXPIRATION_MINUTES = 30;
    const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * Generate payment dengan unique code
     * Workflow:
     * 1. Generate 3-digit unique code
     * 2. Calculate total pembayaran
     * 3. Set expiration time
     * 4. Create payment record
     *
     * @param Booking $booking
     * @param string $paymentType 'full' atau 'partial' (tapi untuk custom system biasanya full)
     * @param array $options Options tambahan (payment_method, dll)
     * @return Payment
     */
    public function generatePayment(Booking $booking, string $paymentType = 'full', array $options = []): Payment
    {
        // Hitung amount berdasarkan payment type
        $amount = $paymentType === 'full' ? $booking->total_price : $booking->total_price / 2;

        // Generate unique code 3 digit
        $uniqueCode = $this->generateUniqueCode();

        // Calculate total pembayaran = amount + unique code
        $totalUnique = $amount + $uniqueCode;

        // Set expiration time 30 menit dari sekarang
        $expiredAt = now()->addMinutes(self::EXPIRATION_MINUTES);

        // Get payment method dari options atau default
        $paymentMethod = $options['payment_method'] ?? 'bank_transfer';
        $paymentDetails = $this->getPaymentDetailsForMethod($paymentMethod);

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'gross_amount' => $amount,
            'payment_type' => $paymentType,
            'payment_method' => $paymentMethod,
            'payment_details' => $paymentDetails,
            'status' => 'pending',
            'unique_code' => $uniqueCode,
            'total_unique' => $totalUnique,
            'expired_at' => $expiredAt,
            'proof_file' => null,
            'approved_by' => null,
            'rejection_reason' => null,
        ]);

        return $payment;
    }

    /**
     * Generate 3-digit unique code
     * Random antara 100-999
     * 
     * @return int
     */
    private function generateUniqueCode(): int
    {
        return rand(self::UNIQUE_CODE_MIN, self::UNIQUE_CODE_MAX);
    }

    /**
     * Get payment details berdasarkan metode pembayaran
     * Details disimpan di JSON untuk flexibility
     *
     * @param string $method
     * @return array|null
     */
    private function getPaymentDetailsForMethod(string $method): ?array
    {
        return match($method) {
            'bank_transfer' => [
                'bank' => self::BANK_ACCOUNT['bank_name'],
                'account_number' => self::BANK_ACCOUNT['account_number'],
                'account_holder' => self::BANK_ACCOUNT['account_holder'],
                'bank_code' => '014',
            ],
            'qrcode_dynamic' => [
                'qris_type' => 'static',
                'qris_id' => '00020126360014ID.CO.TELKOM01051234501520210131ABC31350014ID3020368601051234501520210131ABC3635040512500513100113688001140114ABC000370671005802ID5912PT PADEL IND6013JAKARTA PUSAT99999999999999999999999999999999999999999999999999999999999999999999',
                'qris_merchant' => 'PT. Padel Booking',
                'qris_note' => 'Scan dengan aplikasi bank Anda untuk pembayaran instan',
            ],
            'ewallet' => null,
            'installment' => null,
            default => null,
        };
    }

    /**
     * Check apakah payment sudah expired
     * Jika expired, update status automatically
     *
     * @param Payment $payment
     * @return bool
     */
    public function checkExpiration(Payment $payment): bool
    {
        // Jika sudah expired tapi status belum di-update
        if ($payment->expired_at && now()->isAfter($payment->expired_at)) {
            if ($payment->status === 'pending') {
                $payment->update(['status' => 'expired']);
            }
            return true; // Payment sudah expired
        }

        return false; // Payment masih berlaku
    }

    /**
     * Get time remaining sampai expired (dalam detik)
     * Buat untuk countdown timer di frontend
     *
     * @param Payment $payment
     * @return int (detik)
     */
    public function getTimeRemaining(Payment $payment): int
    {
        if (!$payment->expired_at) {
            return 0;
        }

        $remaining = $payment->expired_at->diffInSeconds(now());
        
        // Jika sudah negatif, return 0
        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Validate dan save proof file
     * Security measures:
     * - Check file extension
     * - Check file size
     * - Rename file dengan hash untuk security
     * - Delete old file jika ada (prevent multiple proofs)
     *
     * @param Payment $payment
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool|string (return filename kalau success, false kalau error)
     */
    public function uploadProof(Payment $payment, $file)
    {
        // 1. Check apakah sudah ada proof (prevent double upload)
        if ($payment->proof_file !== null && $payment->status !== 'pending') {
            return false; // Sudah upload, tidak boleh upload lagi
        }

        // 2. Validate file
        $validator = Validator::make(
            ['proof' => $file],
            [
                'proof' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png',
                    'max:5120', // 5MB
                ],
            ]
        );

        if ($validator->fails()) {
            return false;
        }

        // 3. Check payment status dan expiration sebelum upload
        if ($payment->status !== 'pending') {
            return false; // Payment sudah tidak pending
        }

        if ($this->checkExpiration($payment)) {
            return false; // Payment sudah expired
        }

        // 4. Generate safe filename dengan hashing
        // Format: payment_{id}_{timestamp}_{hash}.{ext}
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $hash = md5($file->getClientOriginalName() . $timestamp);
        $filename = "payment_{$payment->id}_{$timestamp}_{$hash}.{$extension}";

        // 5. Store file di storage/app/payments/
        try {
            $path = $file->storeAs('payments', $filename, 'local');
            
            // 6. Delete old proof kalau ada
            if ($payment->proof_file) {
                Storage::disk('local')->delete('payments/' . $payment->proof_file);
            }

            // 7. Update payment record
            $payment->update(['proof_file' => $filename]);

            return $filename;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment file upload error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get bank account information
     * Untuk display di payment page
     *
     * @return array
     */
    public function getBankAccount(): array
    {
        return self::BANK_ACCOUNT;
    }

    /**
     * Format untuk display di UI
     * 
     * @param Payment $payment
     * @return array
     */
    public function getPaymentDisplay(Payment $payment): array
    {
        return [
            'booking_code' => $payment->booking->booking_code,
            'amount' => $payment->amount,
            'unique_code' => str_pad($payment->unique_code, 3, '0', STR_PAD_LEFT),
            'total_unique' => (int) $payment->total_unique, // Display sebagai integer
            'expired_at' => $payment->expired_at,
            'time_remaining' => $this->getTimeRemaining($payment),
            'status' => $payment->status,
            'is_expired' => $this->checkExpiration($payment),
            'bank' => $this->getBankAccount(),
        ];
    }

    /**
     * Approve payment oleh admin
     * Update status menjadi 'paid' dan set approved_by
     *
     * @param Payment $payment
     * @param int $adminId
     * @return bool
     */
    public function approvePayment(Payment $payment, int $adminId): bool
    {
        // Validasi: hanya status pending yang bisa di-approve
        if ($payment->status !== 'pending') {
            return false;
        }

        try {
            $payment->update([
                'status' => 'paid',
                'approved_by' => $adminId,
                'paid_at' => now(),
            ]);

            // Update booking status menjadi approved
            $payment->booking->update(['status' => 'approved']);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment approval error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject payment oleh admin
     * Update status menjadi 'rejected' dan set reason
     *
     * @param Payment $payment
     * @param string $reason
     * @param int $adminId
     * @return bool
     */
    public function rejectPayment(Payment $payment, string $reason, int $adminId): bool
    {
        // Validasi: hanya status pending yang bisa di-reject
        if ($payment->status !== 'pending') {
            return false;
        }

        // Sanitize reason input
        $reason = strip_tags($reason);
        $reason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

        try {
            $payment->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_by' => $adminId,
            ]);

            // Update booking status kembali ke pending
            $payment->booking->update(['status' => 'pending']);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment rejection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all pending payments untuk admin dashboard
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingPayments()
    {
        return Payment::where('status', 'pending')
            ->with('booking', 'booking.court')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Sanitize input untuk security
     * 
     * @param string $input
     * @return string
     */
    private function sanitizeInput(string $input): string
    {
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return trim($input);
    }
}
