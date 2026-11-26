<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'customer_id',
        'vehicle_id',
        'pickup_date',
        'return_date',
        'total_days',
        'daily_rate',
        'total_amount',
        'status',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'datetime',
            'return_date' => 'datetime',
            'total_days' => 'integer',
            'daily_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }
}
