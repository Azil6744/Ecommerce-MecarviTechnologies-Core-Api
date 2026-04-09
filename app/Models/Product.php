<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'short_description',
        'price',
        'sale_price',
        'cost_price',
        'category_id',
        'stock_quantity',
        'low_stock_threshold',
        'weight',
        'dimensions',
        'images',
        'is_active',
        'is_featured',
        'is_digital',
        'download_url',
        'seo_title',
        'seo_description',
        'tags',
        'attributes',
        'variants',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'tags' => 'array',
        'attributes' => 'array',
        'variants' => 'array',
    ];

    // Relationship with category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with reviews
    public function reviews()
    {
        return $this->hasMany(EcommerceReview::class);
    }

    // Relationship with cart items
    public function cartItems()
    {
        return $this->hasMany(EcommerceCartItem::class);
    }

    // Relationship with order items
    public function orderItems()
    {
        return $this->hasMany(EcommerceOrderItem::class);
    }
}