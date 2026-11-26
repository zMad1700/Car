<x-mail::message>
# Car Rental — Booking {{ ucfirst($booking->status) }}

Hello **{{ $booking->customer->name }}**,

@if ($booking->status === 'cancelled')
We regret to inform you that your booking has been **cancelled**.
@if ($booking->cancellation_reason)

### Reason for Cancellation
<x-mail::panel>
{{ $booking->cancellation_reason }}
</x-mail::panel>
@endif
@else
Thank you for booking with **Car Rental**. Here are your official booking receipt details:
@endif

<x-mail::panel>
**Reference Code:** `{{ $booking->reference_code }}`  
**Status:** {{ strtoupper($booking->status) }}  
**Booking Date:** {{ $booking->created_at?->format('M d, Y h:i A') }}
</x-mail::panel>

### Customer Details
- **Full Name:** {{ $booking->customer->name }}
- **Email:** {{ $booking->customer->email }}
- **Contact Number:** {{ $booking->customer->contact_number }}
- **Address:** {{ $booking->customer->address }}

### Vehicle Details
- **Vehicle:** {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }} ({{ $booking->vehicle->year }})
- **Category:** {{ $booking->vehicle->category->name ?? 'N/A' }}
- **Plate Number:** {{ $booking->vehicle->plate_number }}
- **Transmission:** {{ ucfirst($booking->vehicle->transmission) }}

### Schedule & Summary
- **Pickup Date & Time:** {{ $booking->pickup_date?->format('M d, Y h:i A') }}
- **Return Date & Time:** {{ $booking->return_date?->format('M d, Y h:i A') }}
- **Daily Rate:** PHP {{ number_format((float) $booking->daily_rate, 2) }}
- **Total Days:** {{ $booking->total_days }} day(s)
- **Total Amount:** **PHP {{ number_format((float) $booking->total_amount, 2) }}**

@if ($booking->status !== 'cancelled')
### Important Reminders
- Please bring a **valid government ID** and your **driver's license** upon pickup.
- **Late return penalty applies** if the vehicle is returned past the scheduled return date and time.
- Keep your reference code (`{{ $booking->reference_code }}`) and email handy to track your booking status on our website anytime.
@else
If you wish to submit a new reservation with updated details, please visit our website anytime.
@endif

Thanks,  
{{ config('app.name', 'Car Rental') }}
</x-mail::message>
