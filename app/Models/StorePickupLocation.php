<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePickupLocation extends Model
{
    use HasFactory;

    protected $table = 'store_pickup_locations';

    protected $fillable = [
        'name',
        'code',
        'store_type',
        'timezone',
        'address',
        'phone',
        'notes',
        'short_description',
        'image_path',
        'status',
        'is_pickup_enabled',
        'pickup_preparation_time',
        'pickup_preparation_unit',
        'max_pickup_radius',
        'latitude',
        'longitude',
        'weekly_schedule',
        'special_hours',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_pickup_enabled' => 'boolean',
        'pickup_preparation_time' => 'integer',
        'max_pickup_radius' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'weekly_schedule' => 'array',
        'special_hours' => 'array',
    ];

    /**
     * Scope to get only active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get only pickup enabled locations.
     */
    public function scopePickupEnabled($query)
    {
        return $query->where('is_pickup_enabled', true);
    }
}
