<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'released_by',
        'date_out',
        'date_in',
        'odometer_out',
        'odometer_in',
        'penalty_amount',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_out' => 'datetime',
            'date_in' => 'datetime',
            'odometer_out' => 'integer',
            'odometer_in' => 'integer',
            'penalty_amount' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
