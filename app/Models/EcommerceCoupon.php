<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'subtitle',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'ecommerce_coupon_product', 'coupon_id', 'product_id');
    }

    public function isUsableFor(float $subtotal): bool
    {
        if (! $this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;

        return $subtotal >= (float) $this->min_order_amount;
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isUsableFor($subtotal)) return 0;

        if ($this->discount_type === 'percentage') {
            return round($subtotal * ((float) $this->discount_value / 100), 2);
        }

        return min($subtotal, round((float) $this->discount_value, 2));
    }
}
