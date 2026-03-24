<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTab extends Model
{
    use HasFactory;

    protected $fillable = [
        'tab_name',
        'order',
        'layout_type',
    ];

    public function items()
    {
        return $this->hasMany(ProductItem::class)->orderBy('order');
    }
}
