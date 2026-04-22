<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function courts()
    {
        // only show courts that are currently active (available for booking)
        $courts = Court::where('status', 'active')->get();
        return view('courts.index', compact('courts'));
    }

    public function trackBooking()
    {
        return view('track-booking');
    }

    public function searchBooking(Request $request)
    {
        $request->validate([
            'booking_code' => 'required|string'
        ]);

        $booking = Booking::where('booking_code', $request->booking_code)->first();

        if (!$booking) {
            return redirect()->route('track-booking')->with('error', 'Booking tidak ditemukan. Pastikan nomor referensi Anda benar.');
        }

        return redirect()->route('booking.detail', $booking);
    }

    public function dashboard()
    {
        $bookings = Booking::with(['court', 'timeSlot', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard', compact('bookings'));
    }
}
