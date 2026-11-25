<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\VehicleCategoryController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [VehicleCategoryController::class, 'index']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);

Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/bookings/track', [BookingController::class, 'track']);
Route::get('/bookings/{referenceCode}/receipt', [BookingController::class, 'receipt']);

// Protected admin routes
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('categories', VehicleCategoryController::class);
    Route::apiResource('vehicles', VehicleController::class);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::get('/rentals', [RentalController::class, 'index']);
    Route::post('/rentals/release', [RentalController::class, 'release']);
    Route::post('/rentals/{rental}/return', [RentalController::class, 'returnVehicle']);

    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);

    Route::apiResource('maintenance', MaintenanceController::class)->except(['show']);

    Route::get('/reports/income', [ReportController::class, 'income']);
    Route::get('/reports/most-rented', [ReportController::class, 'mostRentedVehicles']);
    Route::get('/reports/overdue', [ReportController::class, 'overdueRentals']);
});
