<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Maintenance::with('vehicle.category');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->integer('vehicle_id'));
        }

        return response()->json($query->orderByDesc('date_started')->orderByDesc('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'description' => ['required', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'date_started' => ['required', 'date'],
            'date_finished' => ['nullable', 'date', 'after_or_equal:date_started'],
            'status' => ['sometimes', Rule::in(['ongoing', 'done'])],
        ]);

        $status = $validated['status'] ?? 'ongoing';

        $maintenance = DB::transaction(function () use ($validated, $status) {
            $record = Maintenance::create([
                'vehicle_id' => $validated['vehicle_id'],
                'description' => $validated['description'],
                'cost' => $validated['cost'],
                'date_started' => $validated['date_started'],
                'date_finished' => $validated['date_finished'] ?? null,
                'status' => $status,
            ]);

            $this->syncVehicleStatus($record->vehicle);

            return $record;
        });

        $maintenance->load('vehicle.category');

        return response()->json($maintenance, 201);
    }

    public function update(Request $request, Maintenance $maintenance): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'exists:vehicles,id'],
            'description' => ['required', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'date_started' => ['required', 'date'],
            'date_finished' => ['nullable', 'date', 'after_or_equal:date_started'],
            'status' => ['required', Rule::in(['ongoing', 'done'])],
        ]);

        DB::transaction(function () use ($maintenance, $validated) {
            $previousVehicle = $maintenance->vehicle;
            $maintenance->update($validated);
            $maintenance->refresh();

            $this->syncVehicleStatus($maintenance->vehicle);
            if ($previousVehicle->id !== $maintenance->vehicle_id) {
                $this->syncVehicleStatus($previousVehicle);
            }
        });

        $maintenance->load('vehicle.category');

        return response()->json($maintenance);
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        DB::transaction(function () use ($maintenance) {
            $vehicle = $maintenance->vehicle;
            $maintenance->delete();
            $this->syncVehicleStatus($vehicle);
        });

        return response()->json([
            'message' => 'Maintenance record deleted successfully.',
        ]);
    }

    private function syncVehicleStatus(Vehicle $vehicle): void
    {
        $hasOngoing = $vehicle->maintenances()->where('status', 'ongoing')->exists();

        if ($hasOngoing) {
            $vehicle->update(['status' => 'maintenance']);
            return;
        }

        if ($vehicle->status === 'maintenance') {
            $vehicle->update(['status' => 'available']);
        }
    }
}
