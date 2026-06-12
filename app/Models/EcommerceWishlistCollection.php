<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceWishlistCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_wishlist_id',
        'name',
        'slug',
        'sort_order',
    ];

    public function wishlist()
    {
        return $this->belongsTo(EcommerceWishlist::class, 'ecommerce_wishlist_id');
    }

    public function items()
    {
        return $this->hasMany(EcommerceWishlistItem::class, 'ecommerce_wishlist_collection_id');
    }
}
