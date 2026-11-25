<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['booking.customer', 'booking.vehicle']);

        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->integer('booking_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->string('method'));
        }

        return response()->json($query->orderByDesc('paid_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', Rule::in(['deposit', 'rental', 'penalty'])],
            'method' => ['required', Rule::in(['cash', 'gcash', 'card'])],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment = Payment::create([
            'booking_id' => $validated['booking_id'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'method' => $validated['method'],
            'receipt_number' => $this->generateReceiptNumber(),
            'paid_at' => isset($validated['paid_at']) ? Carbon::parse($validated['paid_at']) : now(),
        ]);

        $payment->load(['booking.customer', 'booking.vehicle']);

        return response()->json($payment, 201);
    }

    private function generateReceiptNumber(): string
    {
        $datePart = now()->format('Ymd');

        do {
            $number = 'RCP-' . $datePart . '-' . strtoupper(Str::random(4));
        } while (Payment::where('receipt_number', $number)->exists());

        return $number;
    }
}
