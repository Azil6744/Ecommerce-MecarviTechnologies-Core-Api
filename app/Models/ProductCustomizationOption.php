<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCustomizationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'option_type',
        'option_key',
        'label',
        'price_modifier',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
