<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'attributes' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(EcommerceCart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
