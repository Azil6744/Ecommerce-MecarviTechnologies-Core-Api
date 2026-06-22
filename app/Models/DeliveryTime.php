<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTime extends Model
{
    use HasFactory;

    protected $table = 'delivery_times';

    protected $fillable = [
        'label',
        'estimated_days',
        'description',
        'color_code',
        'pricing',
        'priority',
        'status',
    ];

    protected $casts = [
        'pricing' => 'decimal:2',
        'priority' => 'integer',
        'status' => 'boolean',
    ];
}
