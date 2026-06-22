<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductBrand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'website_url',
        'priority',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }
}
