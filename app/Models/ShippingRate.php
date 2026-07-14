<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $table = 'shipping_rates';

    protected $fillable = [
        'shipping_zone_id',
        'name',
        'rate_type',
        'min_value',
        'max_value',
        'rate_amount',
        'is_active',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'rate_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
