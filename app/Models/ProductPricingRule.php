<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'rule_type',
        'min_quantity',
        'max_quantity',
        'option_type',
        'option_key',
        'adjustment_type',
        'adjustment_value',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
