<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPageHeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_title',
        'description_title',
        'hero_description',
        'section_bg_color',
        'image_url',
        'background_image',
    ];
}
