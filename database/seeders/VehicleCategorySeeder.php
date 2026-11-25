<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sedan',
                'description' => 'Comfortable 4-door cars ideal for city driving and daily commutes.',
            ],
            [
                'name' => 'SUV',
                'description' => 'Spacious sport utility vehicles suited for family trips and rough roads.',
            ],
            [
                'name' => 'Van',
                'description' => 'High-capacity passenger vans perfect for group travel and airport transfers.',
            ],
            [
                'name' => 'Hatchback',
                'description' => 'Compact, fuel-efficient vehicles easy to park in urban areas.',
            ],
        ];

        foreach ($categories as $category) {
            VehicleCategory::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
