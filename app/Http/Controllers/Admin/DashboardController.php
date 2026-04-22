<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        // count bookings scheduled for today
        $bookingToday = Booking::whereDate('date', $today)->count();
        // revenue should be based on actual payments received today
        $revenueToday = Payment::whereDate('created_at', $today)->sum('amount');
        $totalCourts = Court::count();
        // build revenue for each of last 7 days using payments
        $revenue7Days = Payment::where('created_at', '>=', $today->copy()->subDays(6))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');
            $found = $revenue7Days->firstWhere('date', $date);
            $chartData[$date] = $found ? $found->total : 0;
        }
        return view('admin.dashboard.index', compact('bookingToday', 'revenueToday', 'totalCourts', 'chartData'));
    }
}
