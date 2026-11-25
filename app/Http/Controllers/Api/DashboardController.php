<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();

        return response()->json([
            'total_vehicles' => Vehicle::count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'active_rentals' => Rental::whereIn('status', ['active', 'overdue'])->count(),
            'todays_pickups' => Booking::whereDate('pickup_date', today())
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'income_this_month' => (float) Payment::whereYear('paid_at', $now->year)
                ->whereMonth('paid_at', $now->month)
                ->sum('amount'),
            'recent_bookings' => Booking::with(['customer', 'vehicle'])
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
