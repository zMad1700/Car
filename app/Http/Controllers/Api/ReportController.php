<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function income(Request $request): JsonResponse
    {
        $query = Payment::with(['booking.customer', 'booking.vehicle']);

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date('date_to'));
        }

        $payments = $query->orderByDesc('paid_at')->get();

        return response()->json([
            'total_income' => (float) $payments->sum('amount'),
            'by_type' => [
                'deposit' => (float) $payments->where('type', 'deposit')->sum('amount'),
                'rental' => (float) $payments->where('type', 'rental')->sum('amount'),
                'penalty' => (float) $payments->where('type', 'penalty')->sum('amount'),
                'refund' => (float) $payments->where('type', 'refund')->sum('amount'),
            ],
            'by_method' => [
                'cash' => (float) $payments->where('method', 'cash')->sum('amount'),
                'gcash' => (float) $payments->where('method', 'gcash')->sum('amount'),
                'card' => (float) $payments->where('method', 'card')->sum('amount'),
            ],
            'payments' => $payments,
        ]);
    }

    public function mostRentedVehicles(): JsonResponse
    {
        $vehicles = Vehicle::with('category')
            ->withCount([
                'bookings as rental_count' => function ($query) {
                    $query->whereIn('status', ['confirmed', 'completed']);
                },
            ])
            ->withSum([
                'bookings as total_revenue' => function ($query) {
                    $query->whereIn('status', ['confirmed', 'completed']);
                },
            ], 'total_amount')
            ->orderByDesc('rental_count')
            ->get();

        return response()->json($vehicles);
    }

    public function overdueRentals(): JsonResponse
    {
        $rentals = Rental::with(['booking.customer', 'booking.vehicle.category', 'releasedBy'])
            ->whereNull('date_in')
            ->whereHas('booking', function ($query) {
                $query->where('return_date', '<', now());
            })
            ->orderBy('date_out')
            ->get();

        return response()->json($rentals);
    }
}
