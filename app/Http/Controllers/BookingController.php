<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function show(Court $court)
    {
        return view('booking.show', compact('court'));
    }

    public function selectDateTime(Request $request, Court $court)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $timeSlots = TimeSlot::orderBy('start_time')->get()->unique('start_time');

        // Collect ALL booked slots for this court and date
        $bookings = Booking::where('court_id', $court->id)
            ->whereDate('date', $validated['date'])
            ->get(['time_slot_ids']);
        
        $bookedSlotIds = [];
        foreach ($bookings as $booking) {
            if ($booking->time_slot_ids && is_array($booking->time_slot_ids)) {
                $bookedSlotIds = array_merge($bookedSlotIds, $booking->time_slot_ids);
            }
            // Fallback untuk booking lama yang hanya punya time_slot_id
            elseif ($booking->time_slot_id) {
                $bookedSlotIds[] = $booking->time_slot_id;
            }
        }
        $bookedSlotIds = array_unique($bookedSlotIds);
        
        return view('booking.select-datetime', [
            'court' => $court,
            'date' => $validated['date'],
            'timeSlots' => $timeSlots,
            'bookedSlotIds' => $bookedSlotIds,
        ]);
    }

    public function confirm(Request $request, Court $court)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_slot_ids' => 'required|array|min:1',
            'time_slot_ids.*' => 'exists:time_slots,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
        ]);

        // Get all selected time slots
        $selectedSlots = TimeSlot::whereIn('id', $validated['time_slot_ids'])
            ->orderBy('start_time')
            ->get();

        if ($selectedSlots->isEmpty()) {
            return back()->withErrors(['time_slot_ids' => 'Slot waktu tidak valid']);
        }

        // Calculate total price based on number of hours (slots)
        $durationHours = $selectedSlots->count();
        $totalPrice = $court->price_per_hour * $durationHours;
        
        // Get the earliest start time
        $startTime = $selectedSlots->first()->start_time;

        // Create booking
        $booking = Booking::create([
            'booking_code' => 'BKG-' . strtoupper(Str::random(8)),
            'court_id' => $court->id,
            'user_id' => Auth::id(),
            'time_slot_id' => $selectedSlots->first()->id,
            'time_slot_ids' => $validated['time_slot_ids'], // Save all selected slot IDs as JSON
            'date' => $validated['date'],
            'duration_hours' => $durationHours,
            'start_time' => $startTime,
            'customer_name' => $validated['customer_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'total_price' => $totalPrice,
            'paid' => 0,
            'remaining' => $totalPrice,
            'status' => 'pending',
        ]);

        return redirect()->route('booking.detail', $booking);
    }

    public function detail(Booking $booking)
    {
        return view('booking.detail', compact('booking')); 
    }

    /**
     * Simple receipt view for printing or download
     */
    public function receipt(Booking $booking)
    {
        // reuse detail view or create a separate minimal view
        return view('booking.receipt', compact('booking'));
    }
}
