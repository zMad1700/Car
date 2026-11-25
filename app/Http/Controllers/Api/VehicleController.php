<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->string('transmission'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', $search)
                    ->orWhere('model', 'like', $search)
                    ->orWhere('plate_number', 'like', $search);
            });
        }

        $vehicles = $query->orderByDesc('id')->get();

        return response()->json($vehicles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateVehicle($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $validated['status'] = $validated['status'] ?? 'available';

        $vehicle = Vehicle::create($validated);
        $vehicle->refresh()->load('category');

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['category', 'maintenances']);

        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $this->validateVehicle($request, $vehicle->id);

        if ($request->hasFile('image')) {
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);
        $vehicle->load('category');

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->bookings()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a vehicle with existing bookings.',
            ], 422);
        }

        if ($vehicle->image) {
            Storage::disk('public')->delete($vehicle->image);
        }

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    private function validateVehicle(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:vehicle_categories,id'],
            'plate_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'plate_number')->ignore($ignoreId),
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'color' => ['required', 'string', 'max:50'],
            'transmission' => ['required', Rule::in(['manual', 'automatic'])],
            'seats' => ['required', 'integer', 'min:1', 'max:30'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['available', 'booked', 'rented', 'maintenance'])],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
