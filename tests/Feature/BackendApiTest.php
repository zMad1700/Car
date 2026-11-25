<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BackendApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_endpoints_require_sanctum_authentication(): void
    {
        $this->get('/api/admin/dashboard')->assertStatus(401);
        $this->post('/api/admin/categories', ['name' => 'Test'])->assertStatus(401);
    }

    public function test_admin_can_login_and_non_admin_is_rejected(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@carrental.com',
            'password' => 'password',
        ]);

        $staff = User::factory()->create([
            'email' => 'staff@carrental.com',
            'password' => 'password',
            'role' => 'staff',
        ]);

        $this->postJson('/api/login', [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertStatus(403);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
    }

    public function test_public_booking_overlap_maintenance_tracking_and_admin_workflow(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $category = VehicleCategory::factory()->create(['name' => 'Sedan']);
        $vehicle = Vehicle::factory()->create([
            'category_id' => $category->id,
            'daily_rate' => 2000,
            'status' => 'available',
        ]);

        // Public vehicles listing
        $this->getJson('/api/vehicles?category_id=' . $category->id)
            ->assertStatus(200)
            ->assertJsonCount(1);

        $pickup = now()->addDay()->setTime(9, 0);
        $return = now()->addDays(3)->setTime(9, 0); // 2 days => 4000

        // Create booking
        $bookingRes = $this->postJson('/api/bookings', [
            'vehicle_id' => $vehicle->id,
            'name' => 'Juan Dela Cruz',
            'address' => 'Cebu City',
            'contact_number' => '09171234567',
            'email' => 'juan@gmail.com',
            'pickup_date' => $pickup->toDateTimeString(),
            'return_date' => $return->toDateTimeString(),
            'terms_accepted' => true,
        ]);

        $bookingRes->assertStatus(201)
            ->assertJsonPath('total_days', 2)
            ->assertJsonPath('total_amount', '4000.00')
            ->assertJsonPath('status', 'pending');

        $referenceCode = $bookingRes->json('reference_code');
        $this->assertMatchesRegularExpression('/^BK-\d{8}-[A-Z0-9]{4}$/', $referenceCode);

        // Pending booking does not send receipt yet until admin confirms
        Mail::assertNothingSent();

        // Overlapping booking must be rejected
        $this->postJson('/api/bookings', [
            'vehicle_id' => $vehicle->id,
            'name' => 'Maria Santos',
            'address' => 'Davao City',
            'contact_number' => '09189999999',
            'email' => 'maria@gmail.com',
            'pickup_date' => $pickup->copy()->addHours(2)->toDateTimeString(),
            'return_date' => $return->copy()->subHours(2)->toDateTimeString(),
            'terms_accepted' => true,
        ])->assertStatus(422);

        // Track booking requires matching reference code and email
        $this->postJson('/api/bookings/track', [
            'reference_code' => $referenceCode,
            'email' => 'wrong@gmail.com',
        ])->assertStatus(404);

        $this->postJson('/api/bookings/track', [
            'reference_code' => $referenceCode,
            'email' => 'juan@gmail.com',
        ])->assertStatus(200)
            ->assertJsonPath('reference_code', $referenceCode);

        // Receipt endpoint
        $this->getJson('/api/bookings/' . $referenceCode . '/receipt')
            ->assertStatus(200)
            ->assertJsonPath('customer.email', 'juan@gmail.com');

        // Admin workflow: confirm (sends receipt email) -> release -> return late -> payment -> maintenance -> dashboard & reports
        Sanctum::actingAs($admin);

        $booking = Booking::where('reference_code', $referenceCode)->firstOrFail();

        $this->postJson("/api/admin/bookings/{$booking->id}/confirm")
            ->assertStatus(200)
            ->assertJsonPath('status', 'confirmed');

        Mail::assertSent(BookingConfirmation::class, function (BookingConfirmation $mail) use ($referenceCode) {
            $html = $mail->render();
            return str_contains($html, $referenceCode) && str_contains($html, 'Juan Dela Cruz');
        });
        $this->assertDatabaseHas('email_logs', [
            'sent_to' => 'juan@gmail.com',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'status' => 'booked',
        ]);

        $releaseRes = $this->postJson('/api/admin/rentals/release', [
            'booking_id' => $booking->id,
            'date_out' => $pickup->toDateTimeString(),
            'odometer_out' => 15000,
            'remarks' => 'Full tank',
        ]);
        $releaseRes->assertStatus(201)->assertJsonPath('status', 'active');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'status' => 'rented',
        ]);

        $rentalId = $releaseRes->json('id');

        // Return 1 day late -> penalty = 2000.00
        $this->postJson("/api/admin/rentals/{$rentalId}/return", [
            'date_in' => $return->copy()->addDay()->toDateTimeString(),
            'odometer_in' => 15350,
            'remarks' => 'Returned 1 day late',
        ])->assertStatus(200)
            ->assertJsonPath('status', 'returned')
            ->assertJsonPath('penalty_amount', '2000.00');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'status' => 'available',
        ]);

        // Record payment
        $this->postJson('/api/admin/payments', [
            'booking_id' => $booking->id,
            'amount' => 6000,
            'type' => 'rental',
            'method' => 'gcash',
        ])->assertStatus(201)
            ->assertJsonPath('method', 'gcash');

        // Maintenance sets vehicle to maintenance and blocks booking
        $maintRes = $this->postJson('/api/admin/maintenance', [
            'vehicle_id' => $vehicle->id,
            'description' => 'Change oil',
            'cost' => 1500,
            'date_started' => now()->toDateString(),
            'status' => 'ongoing',
        ]);
        $maintRes->assertStatus(201);
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'status' => 'maintenance',
        ]);

        // Dashboard & reports
        $this->getJson('/api/admin/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('total_vehicles', 1);

        $this->getJson('/api/admin/reports/income')
            ->assertStatus(200)
            ->assertJsonPath('total_income', 6000);

        $this->getJson('/api/admin/reports/most-rented')->assertStatus(200);
        $this->getJson('/api/admin/reports/overdue')->assertStatus(200);
    }
}
