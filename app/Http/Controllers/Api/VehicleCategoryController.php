<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = VehicleCategory::withCount('vehicles')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = VehicleCategory::create($validated);

        return response()->json($category, 201);
    }

    public function show(VehicleCategory $category): JsonResponse
    {
        $category->load('vehicles');

        return response()->json($category);
    }

    public function update(Request $request, VehicleCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(VehicleCategory $category): JsonResponse
    {
        if ($category->vehicles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that still has vehicles.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
