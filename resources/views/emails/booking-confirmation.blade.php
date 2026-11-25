<x-mail::message>
# Car Rental — Booking {{ ucfirst($booking->status) }}

Hello **{{ $booking->customer->name }}**,

Thank you for booking with **Car Rental**. Here are your booking details:

<x-mail::panel>
**Reference Code:** `{{ $booking->reference_code }}`  
**Status:** {{ strtoupper($booking->status) }}  
**Booking Date:** {{ $booking->created_at?->format('M d, Y h:i A') }}
</x-mail::panel>

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

### Important Reminders
- Please bring a **valid government ID** and your **driver's license** upon pickup.
- **Late return penalty applies** if the vehicle is returned past the scheduled return date and time.

<x-mail::button :url="url('/track')">
Track Your Booking
</x-mail::button>

Thanks,  
{{ config('app.name', 'Car Rental') }}
</x-mail::message>
