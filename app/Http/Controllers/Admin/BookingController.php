<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query();
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        $bookings = $query->with(['court', 'timeSlot', 'payments'])->orderBy('date', 'desc')->get();
        return view('admin.bookings.index', compact('bookings'));
    }
    public function show(Booking $booking)
    {
        $booking->load(['court', 'timeSlot', 'payments']);
        return view('admin.bookings.show', compact('booking'));
    }
    public function destroy(Booking $booking)
    {
        try {
            $booking->delete();
            return redirect()->route('admin.bookings.index')
                ->with('success', "Booking '{$booking->booking_code}' and associated payments deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->route('admin.bookings.index')
                ->with('error', "Failed to delete booking: " . $e->getMessage());
        }
    }

    /**
     * Approve a pending payment for a booking.
     */
    public function approvePayment(Booking $booking, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('warning', 'Payment is already processed.');
        }

        $payment->status = 'completed';
        $payment->save();

        // update booking paid/remaining/status
        $booking->paid += $payment->amount;
        $booking->remaining = max(0, $booking->total_price - $booking->paid);
        if ($booking->paid >= $booking->total_price) {
            $booking->status = 'approved';
        } elseif ($booking->paid > 0) {
            $booking->status = 'partial';
        }
        $booking->save();

        return back()->with('success', 'Payment approved and booking updated.');
    }
}
