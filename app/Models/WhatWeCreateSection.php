<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatWeCreateSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_title',
        'background_image',
        'section_bg_color',
        'card_background_color',
        'heading_title',
        'description',
        'button_text',
        'button_url',
        'grid_background_color',
        'grid_card_background_color',
        'grid_background_image',
    ];

    /**
     * Get the full URL for the background image.
     *
     * @return string|null
     */
    public function getBackgroundImageUrlAttribute()
    {
        if ($this->background_image) {
            return asset('storage/' . $this->background_image);
        }
        return null;
    }

    /**
     * Get the full URL for the grid background image.
     *
     * @return string|null
     */
    public function getGridBackgroundImageUrlAttribute()
    {
        if ($this->grid_background_image) {
            return asset('storage/' . $this->grid_background_image);
        }
        return null;
    }
}
