<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceCartItem extends Model
{
    use HasFactory;

    protected $appends = [
        'price',
        'total',
    ];

    protected $fillable = [
        'ecommerce_cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'options',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'options' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(EcommerceCart::class, 'ecommerce_cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceAttribute()
    {
        return $this->unit_price;
    }

    public function getTotalAttribute()
    {
        return $this->total_price;
    }

}
