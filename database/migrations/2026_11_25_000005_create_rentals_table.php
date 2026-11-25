<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('released_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('date_out');
            $table->dateTime('date_in')->nullable();
            $table->unsignedInteger('odometer_out');
            $table->unsignedInteger('odometer_in')->nullable();
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
