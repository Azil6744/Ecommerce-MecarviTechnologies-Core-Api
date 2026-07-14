<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $table = 'shipping_zones';

    protected $fillable = [
        'name',
        'regions',
        'is_active',
    ];

    protected $casts = [
        'regions' => 'array',
        'is_active' => 'boolean',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class, 'shipping_zone_id');
    }
}
