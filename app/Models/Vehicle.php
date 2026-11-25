<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'transmission',
        'seats',
        'daily_rate',
        'image',
        'interior_image',
        'status',
    ];

    protected $appends = [
        'image_url',
        'interior_image_url',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'seats' => 'integer',
            'daily_rate' => 'decimal:2',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveAssetUrl($this->image),
        );
    }

    protected function interiorImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveAssetUrl($this->interior_image),
        );
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, 'images/')) {
            return '/' . $path;
        }

        return '/storage/' . ltrim($path, '/');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
