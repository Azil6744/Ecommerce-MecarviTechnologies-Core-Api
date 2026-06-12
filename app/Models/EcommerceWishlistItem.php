<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceWishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_wishlist_id',
        'ecommerce_wishlist_collection_id',
        'product_id',
        'quantity',
        'saved_price',
        'options',
        'product_snapshot',
    ];

    protected $casts = [
        'saved_price' => 'decimal:2',
        'options' => 'array',
        'product_snapshot' => 'array',
    ];

    public function wishlist()
    {
        return $this->belongsTo(EcommerceWishlist::class, 'ecommerce_wishlist_id');
    }

    public function collection()
    {
        return $this->belongsTo(EcommerceWishlistCollection::class, 'ecommerce_wishlist_collection_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
