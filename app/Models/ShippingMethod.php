<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'base_rate',
        'estimated_days', 'coverage', 'is_active',
        'free_shipping_threshold', 'settings', 'sort_order',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];
}
