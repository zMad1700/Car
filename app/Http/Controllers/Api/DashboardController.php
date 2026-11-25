<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();

        $statusCounts = Vehicle::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $amount = (float) Payment::whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');

            $monthlyRevenue[] = [
                'label' => $month->format('M Y'),
                'short_label' => $month->format('M'),
                'amount' => $amount,
            ];
        }

        return response()->json([
            'total_vehicles' => Vehicle::count(),
            'available_vehicles' => (int) ($statusCounts['available'] ?? 0),
            'rented_vehicles' => (int) ($statusCounts['rented'] ?? 0),
            'booked_vehicles' => (int) ($statusCounts['booked'] ?? 0),
            'maintenance_vehicles' => (int) ($statusCounts['maintenance'] ?? 0),
            'total_customers' => Customer::count(),
            'active_rentals' => Rental::whereIn('status', ['active', 'overdue'])->count(),
            'todays_pickups' => Booking::whereDate('pickup_date', today())
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'income_this_month' => (float) Payment::whereYear('paid_at', $now->year)
                ->whereMonth('paid_at', $now->month)
                ->sum('amount'),
            'total_revenue' => (float) Payment::sum('amount'),
            'status_breakdown' => [
                'available' => (int) ($statusCounts['available'] ?? 0),
                'booked' => (int) ($statusCounts['booked'] ?? 0),
                'rented' => (int) ($statusCounts['rented'] ?? 0),
                'maintenance' => (int) ($statusCounts['maintenance'] ?? 0),
            ],
            'monthly_revenue' => $monthlyRevenue,
            'recent_bookings' => Booking::with(['customer', 'vehicle'])
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'recent_rentals' => Rental::with(['booking.customer', 'booking.vehicle'])
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
