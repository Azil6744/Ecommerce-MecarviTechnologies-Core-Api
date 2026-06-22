<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLabel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color_code',
        'display_order',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];
}
