<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_slug',
        'section',
        'label',
        'name',
        'type',
        'options',
        'config',
        'is_required',
        'sort_order',
        'is_active',
        'placeholder',
        'grid_cols',
    ];

    protected $casts = [
        'options' => 'array',
        'config' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'grid_cols' => 'integer',
    ];
}
