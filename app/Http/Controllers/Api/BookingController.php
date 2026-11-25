<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['customer', 'vehicle.category', 'rental', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('pickup_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('pickup_date', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', $search)
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        return response()->json($query->orderByDesc('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'contact_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'pickup_date' => ['required', 'date', 'after_or_equal:' . now()->subMinutes(5)->toDateTimeString()],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'terms_accepted' => ['required', 'accepted'],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $pickupDate = Carbon::parse($validated['pickup_date']);
        $returnDate = Carbon::parse($validated['return_date']);

        if ($vehicle->status === 'maintenance' || $vehicle->maintenances()->where('status', 'ongoing')->exists()) {
            return response()->json([
                'message' => 'This vehicle is currently under maintenance and cannot be booked.',
            ], 422);
        }

        if ($vehicle->status !== 'available') {
            return response()->json([
                'message' => 'Only available vehicles can be booked.',
            ], 422);
        }

        if ($this->hasOverlappingBooking($vehicle->id, $pickupDate, $returnDate)) {
            return response()->json([
                'message' => 'Vehicle is already booked for the selected pickup and return schedule.',
            ], 422);
        }

        $booking = DB::transaction(function () use ($validated, $vehicle, $pickupDate, $returnDate) {
            $customer = Customer::updateOrCreate(
                ['email' => strtolower($validated['email'])],
                [
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'contact_number' => $validated['contact_number'],
                ]
            );

            $totalDays = max(1, (int) ceil($pickupDate->diffInMinutes($returnDate) / 1440));
            $dailyRate = (float) $vehicle->daily_rate;
            $totalAmount = round($totalDays * $dailyRate, 2);

            return Booking::create([
                'reference_code' => $this->generateReferenceCode(),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'total_days' => $totalDays,
                'daily_rate' => $dailyRate,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);
        });

        $booking->load(['customer', 'vehicle.category']);

        $this->sendBookingEmail(
            $booking,
            "Car Rental Booking Receipt - {$booking->reference_code}"
        );

        return response()->json($booking, 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['customer', 'vehicle.category', 'rental.releasedBy', 'payments', 'emailLogs']);

        return response()->json($booking);
    }

    public function receipt(string $referenceCode): JsonResponse
    {
        $booking = Booking::with(['customer', 'vehicle.category'])
            ->where('reference_code', strtoupper(trim($referenceCode)))
            ->firstOrFail();

        return response()->json($booking);
    }

    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference_code' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $booking = Booking::with(['customer', 'vehicle.category', 'rental', 'payments'])
            ->where('reference_code', strtoupper(trim($validated['reference_code'])))
            ->whereHas('customer', function ($query) use ($validated) {
                $query->whereRaw('LOWER(email) = ?', [strtolower(trim($validated['email']))]);
            })
            ->first();

        if (! $booking) {
            return response()->json([
                'message' => 'No booking found matching that reference code and email.',
            ], 404);
        }

        return response()->json($booking);
    }

    public function confirm(Booking $booking): JsonResponse
    {
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be confirmed.',
            ], 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'confirmed']);

            if ($booking->vehicle->status === 'available') {
                $booking->vehicle->update(['status' => 'booked']);
            }
        });

        $booking->load(['customer', 'vehicle.category']);

        $this->sendBookingEmail(
            $booking,
            "Car Rental Booking Confirmed - {$booking->reference_code}"
        );

        return response()->json($booking);
    }

    public function cancel(Booking $booking): JsonResponse
    {
        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return response()->json([
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        if ($booking->rental && $booking->rental->status !== 'returned') {
            return response()->json([
                'message' => 'Cannot cancel a booking with an active rental.',
            ], 422);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            if ($booking->vehicle->status === 'booked') {
                $hasOtherActiveBooking = Booking::where('vehicle_id', $booking->vehicle_id)
                    ->where('id', '!=', $booking->id)
                    ->where('status', 'confirmed')
                    ->exists();

                if (! $hasOtherActiveBooking) {
                    $booking->vehicle->update(['status' => 'available']);
                }
            }
        });

        $booking->load(['customer', 'vehicle.category']);

        $this->sendBookingEmail(
            $booking,
            "Car Rental Booking Cancelled - {$booking->reference_code}"
        );

        return response()->json($booking);
    }

    private function hasOverlappingBooking(int $vehicleId, Carbon $pickupDate, Carbon $returnDate): bool
    {
        return Booking::where('vehicle_id', $vehicleId)
            ->where('status', '!=', 'cancelled')
            ->where('pickup_date', '<', $returnDate)
            ->where('return_date', '>', $pickupDate)
            ->exists();
    }

    private function generateReferenceCode(): string
    {
        $datePart = now()->format('Ymd');

        do {
            $code = 'BK-' . $datePart . '-' . strtoupper(Str::random(4));
        } while (Booking::where('reference_code', $code)->exists());

        return $code;
    }

    private function sendBookingEmail(Booking $booking, string $subject): void
    {
        $recipient = $booking->customer->email;
        $status = 'sent';

        try {
            Mail::to($recipient)->send(new BookingConfirmation($booking, $subject));
        } catch (\Throwable $e) {
            $status = 'failed';
            Log::error('Booking confirmation email failed', [
                'booking_id' => $booking->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }

        EmailLog::create([
            'booking_id' => $booking->id,
            'sent_to' => $recipient,
            'subject' => $subject,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }
}
