<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_name',
        'phone',
        'email',
        'user_id',
        'court_id',
        'date',
        'time_slot_id',
        'time_slot_ids',
        'duration_hours',
        'start_time',
        'total_price',
        'paid',
        'remaining',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'time_slot_ids' => 'array',
        'total_price' => 'decimal:2',
        'paid' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    /**
     * Boot the model – cleanup related payments on delete
     */
    protected static function boot()
    {
        parent::boot();

        // cascade delete payments when booking is deleted
        static::deleting(function ($booking) {
            $booking->payments()->delete();
        });
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all booked time slots
     * Returns the TimeSlot models for all booked slots
     */
    public function bookedTimeSlots()
    {
        if (!$this->time_slot_ids || !is_array($this->time_slot_ids)) {
            return collect();
        }
        return TimeSlot::whereIn('id', $this->time_slot_ids)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get booked time range as formatted string
     * e.g. "09:00 - 10:00, 10:00 - 11:00, 11:00 - 12:00"
     */
    public function getBookedTimeRangeAttribute()
    {
        $slots = $this->bookedTimeSlots();
        if ($slots->isEmpty()) {
            return $this->start_time ? 
                \Carbon\Carbon::parse($this->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($this->start_time)->addHours($this->duration_hours)->format('H:i')
                : 'N/A';
        }
        
        $ranges = $slots->map(function($slot) {
            return $slot->display_text;
        })->join(', ');
        
        return $ranges;
    }
}

