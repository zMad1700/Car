<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = VehicleCategory::pluck('id', 'name');

        $vehicles = [
            [
                'category' => 'Sedan',
                'plate_number' => 'ABC-1001',
                'brand' => 'Toyota',
                'model' => 'Vios',
                'year' => 2023,
                'color' => 'Silver',
                'transmission' => 'automatic',
                'seats' => 5,
                'daily_rate' => 1800.00,
                'image' => 'images/vehicles/vios.jpg',
                'interior_image' => 'images/vehicles/sedan_interior.svg',
                'status' => 'available',
            ],
            [
                'category' => 'Sedan',
                'plate_number' => 'ABC-1003',
                'brand' => 'Honda',
                'model' => 'Civic',
                'year' => 2023,
                'color' => 'Gray',
                'transmission' => 'automatic',
                'seats' => 5,
                'daily_rate' => 2500.00,
                'image' => 'images/vehicles/civic.jpg',
                'interior_image' => 'images/vehicles/sedan_interior.svg',
                'status' => 'maintenance',
            ],
            [
                'category' => 'SUV',
                'plate_number' => 'SUV-2001',
                'brand' => 'Toyota',
                'model' => 'Fortuner',
                'year' => 2024,
                'color' => 'Black',
                'transmission' => 'automatic',
                'seats' => 7,
                'daily_rate' => 3800.00,
                'image' => 'images/vehicles/fortuner.jpg',
                'interior_image' => 'images/vehicles/suv_interior.svg',
                'status' => 'available',
            ],
            [
                'category' => 'SUV',
                'plate_number' => 'SUV-2003',
                'brand' => 'Ford',
                'model' => 'Everest',
                'year' => 2024,
                'color' => 'Blue',
                'transmission' => 'automatic',
                'seats' => 7,
                'daily_rate' => 4000.00,
                'image' => 'images/vehicles/everest.jpg',
                'interior_image' => 'images/vehicles/suv_interior.svg',
                'status' => 'available',
            ],
            [
                'category' => 'Van',
                'plate_number' => 'VAN-3001',
                'brand' => 'Toyota',
                'model' => 'Hiace GL Grandia',
                'year' => 2023,
                'color' => 'White',
                'transmission' => 'manual',
                'seats' => 12,
                'daily_rate' => 4500.00,
                'image' => 'images/vehicles/hiace.jpg',
                'interior_image' => 'images/vehicles/hiace_interior.jpg',
                'interior_image_2' => 'images/vehicles/hiace_cabin.jpg',
                'status' => 'available',
            ],
            [
                'category' => 'Hatchback',
                'plate_number' => 'HAT-4001',
                'brand' => 'Toyota',
                'model' => 'Wigo',
                'year' => 2024,
                'color' => 'Red',
                'transmission' => 'automatic',
                'seats' => 5,
                'daily_rate' => 1400.00,
                'image' => 'images/vehicles/wigo.jpg',
                'interior_image' => 'images/vehicles/hatchback_interior.svg',
                'status' => 'available',
            ],
        ];

        $keepPlates = collect($vehicles)->pluck('plate_number')->all();

        // Remove any old unbooked sample vehicles beyond the 6 display models
        Vehicle::whereNotIn('plate_number', $keepPlates)
            ->whereDoesntHave('bookings')
            ->delete();

        foreach ($vehicles as $data) {
            $categoryId = $categories[$data['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $vehicle = Vehicle::updateOrCreate(
                ['plate_number' => $data['plate_number']],
                [
                    'category_id' => $categoryId,
                    'brand' => $data['brand'],
                    'model' => $data['model'],
                    'year' => $data['year'],
                    'color' => $data['color'],
                    'transmission' => $data['transmission'],
                    'seats' => $data['seats'],
                    'daily_rate' => $data['daily_rate'],
                    'image' => $data['image'],
                    'interior_image' => $data['interior_image'],
                    'interior_image_2' => $data['interior_image_2'] ?? null,
                    'status' => $data['status'],
                ]
            );

            if ($data['status'] === 'maintenance') {
                Maintenance::firstOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'status' => 'ongoing',
                    ],
                    [
                        'description' => 'Scheduled preventive maintenance and brake inspection.',
                        'cost' => 3500.00,
                        'date_started' => now()->toDateString(),
                        'date_finished' => null,
                    ]
                );
            }
        }
    }
}
