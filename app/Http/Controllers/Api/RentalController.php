<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Rental::with(['booking.customer', 'booking.vehicle.category', 'releasedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->orderByDesc('id')->get());
    }

    public function release(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'date_out' => ['required', 'date'],
            'odometer_out' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $booking = Booking::with(['vehicle', 'rental'])->findOrFail($validated['booking_id']);

        if ($booking->rental) {
            return response()->json([
                'message' => 'This booking has already been released as a rental.',
            ], 422);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be released.',
            ], 422);
        }

        if ($booking->vehicle->status === 'maintenance') {
            return response()->json([
                'message' => 'Cannot release a vehicle currently under maintenance.',
            ], 422);
        }

        $rental = DB::transaction(function () use ($validated, $booking, $request) {
            $rental = Rental::create([
                'booking_id' => $booking->id,
                'released_by' => $request->user()->id,
                'date_out' => Carbon::parse($validated['date_out']),
                'odometer_out' => $validated['odometer_out'],
                'penalty_amount' => 0,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'active',
            ]);

            $booking->vehicle->update(['status' => 'rented']);

            return $rental;
        });

        $rental->load(['booking.customer', 'booking.vehicle.category', 'releasedBy']);

        return response()->json($rental, 201);
    }

    public function returnVehicle(Request $request, Rental $rental): JsonResponse
    {
        if ($rental->status === 'returned') {
            return response()->json([
                'message' => 'This rental has already been returned.',
            ], 422);
        }

        $validated = $request->validate([
            'date_in' => ['required', 'date', 'after_or_equal:' . $rental->date_out->toDateTimeString()],
            'odometer_in' => ['required', 'integer', 'min:' . $rental->odometer_out],
            'remarks' => ['nullable', 'string'],
        ]);

        $rental->load(['booking.vehicle']);
        $dateIn = Carbon::parse($validated['date_in']);
        $scheduledReturn = $rental->booking->return_date;

        $penaltyAmount = 0;
        if ($dateIn->gt($scheduledReturn)) {
            $lateDays = max(1, (int) ceil($scheduledReturn->diffInMinutes($dateIn) / 1440));
            $penaltyAmount = round($lateDays * (float) $rental->booking->daily_rate, 2);
        }

        DB::transaction(function () use ($rental, $dateIn, $validated, $penaltyAmount) {
            $rental->update([
                'date_in' => $dateIn,
                'odometer_in' => $validated['odometer_in'],
                'penalty_amount' => $penaltyAmount,
                'remarks' => $validated['remarks'] ?? $rental->remarks,
                'status' => 'returned',
            ]);

            $rental->booking->update(['status' => 'completed']);

            $vehicle = $rental->booking->vehicle;
            $hasOngoingMaintenance = $vehicle->maintenances()->where('status', 'ongoing')->exists();
            $vehicle->update(['status' => $hasOngoingMaintenance ? 'maintenance' : 'available']);
        });

        $rental->load(['booking.customer', 'booking.vehicle.category', 'releasedBy']);

        return response()->json($rental);
    }
}
