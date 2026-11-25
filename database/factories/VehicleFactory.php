<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => VehicleCategory::factory(),
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'brand' => fake()->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Suzuki', 'Ford']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2019, 2025),
            'color' => fake()->safeColorName(),
            'transmission' => fake()->randomElement(['manual', 'automatic']),
            'seats' => fake()->randomElement([4, 5, 7, 12, 15]),
            'daily_rate' => fake()->randomFloat(2, 1200, 4500),
            'image' => null,
            'status' => 'available',
        ];
    }
}
