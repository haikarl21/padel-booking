<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'order_id',
        'amount',
        'status',
        'payment_type',
        'snap_token',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'total_unique' => 'decimal:2',
        'midtrans_response' => 'json', // Casting JSON response
        'payment_details' => 'array',   // Casting JSON payment details
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Relasi: Payment belongsTo Booking
     * Setiap payment terhubung dengan satu booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Helper method untuk cek apakah payment berhasil
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === 'settlement';
    }

    /**
     * Helper method untuk cek apakah payment masih pending
     * 
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Helper method untuk cek apakah payment gagal
     * 
     * @return bool
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'expired']);
    }

    /**
     * Helper method untuk cek apakah payment sudah expired
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expired_at && now()->isAfter($this->expired_at));
    }

    /**
     * Helper method untuk cek apakah payment sudah di-approve
     * 
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === 'paid' && $this->approved_by !== null;
    }

    /**
     * Check apakah payment method adalah bank transfer
     * 
     * @return bool
     */
    public function isBankTransfer(): bool
    {
        return $this->payment_method === 'bank_transfer';
    }

    /**
     * Get payment bank info
     * 
     * @return array|null
     */
    public function getBankInfo(): ?array
    {
        if ($this->payment_details && isset($this->payment_details['bank'])) {
            return $this->payment_details;
        }
        return null;
    }

    /**
     * Get payment method display name
     * 
     * @return string
     */
    public function getMethodDisplayName(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'Bank Transfer Manual',
            'ewallet' => 'E-Wallet',
            'qrcode_dynamic' => 'QRIS/QR Code',
            default => ucwords(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Relasi: Payment approvedBy User (Admin)
     * Siapa yang approve/reject payment ini
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

