<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'hex_code',
        'swatch_image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
