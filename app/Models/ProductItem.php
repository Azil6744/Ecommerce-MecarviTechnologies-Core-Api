<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_tab_id',
        'title',
        'description',
        'image_url',
        'video_url',
        'card_title_one',
        'card_text_one',
        'card_title_two',
        'card_text_two',
        'order',
    ];

    public function tab()
    {
        return $this->belongsTo(ProductTab::class, 'product_tab_id');
    }
}
