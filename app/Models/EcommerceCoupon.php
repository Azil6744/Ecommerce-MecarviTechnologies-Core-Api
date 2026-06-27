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
        'used_count' => 'integer',
        'usage_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'status',
        'is_expired',
        'is_scheduled',
        'usage_remaining',
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

        if ($this->discount_type === 'free_shipping') {
            return 0;
        }

        if ($this->discount_type === 'buy_x_get_y') {
            $reward = (float) ($this->metadata['reward_amount'] ?? $this->metadata['discount_value'] ?? $this->discount_value ?? 0);
            return min($subtotal, round($reward, 2));
        }

        return min($subtotal, round((float) $this->discount_value, 2));
    }

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'paused';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function getIsExpiredAttribute(): bool
    {
        return (bool) ($this->expires_at && $this->expires_at->isPast());
    }

    public function getIsScheduledAttribute(): bool
    {
        return (bool) ($this->starts_at && $this->starts_at->isFuture());
    }

    public function getUsageRemainingAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        return max(0, (int) $this->usage_limit - (int) $this->used_count);
    }

    public function toManagementArray(): array
    {
        return array_merge($this->toPublicArray(), [
            'used_count' => (int) $this->used_count,
            'usage_remaining' => $this->usage_remaining,
            'products' => $this->relationLoaded('products')
                ? $this->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                ])->values()->all()
                : [],
        ]);
    }

    public function toPublicArray(): array
    {
        $metadata = $this->metadata ?? [];

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'min_order_amount' => (float) $this->min_order_amount,
            'usage_limit' => $this->usage_limit,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'is_expired' => $this->is_expired,
            'is_scheduled' => $this->is_scheduled,
            'metadata' => $metadata,
            'display' => [
                'note' => $metadata['note'] ?? ($this->expires_at ? 'Valid till: ' . $this->expires_at->toFormattedDateString() : 'Valid offer'),
                'side' => $metadata['side'] ?? null,
                'badge' => $this->displayBadge(),
            ],
        ];
    }

    public function displayBadge(): string
    {
        if ($this->discount_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.') . '% OFF';
        }

        if ($this->discount_type === 'fixed') {
            return '$' . rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.') . ' OFF';
        }

        if ($this->discount_type === 'free_shipping') {
            return 'FREE SHIPPING';
        }

        return 'BUY X GET Y';
    }
}
