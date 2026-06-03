<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCustomizationDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'session_id',
        'selected_options',
        'quantity',
        'unit_price',
        'setup_fee',
        'discount_amount',
        'total_price',
        'coupon_code',
        'status',
        'metadata',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'metadata' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function files()
    {
        return $this->hasMany(ProductCustomizationFile::class, 'draft_id');
    }
}
