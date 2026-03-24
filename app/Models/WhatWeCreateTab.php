<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatWeCreateTab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_tab_id',
        'tag_label',
        'main_heading',
        'description',
        'features',
        'button_text',
        'image_1',
        'image_2',
        'image_3',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'features' => 'array',
    ];

    /**
     * Get the full URL for image_1.
     *
     * @return string|null
     */
    public function getImage1UrlAttribute()
    {
        if ($this->image_1) {
            return asset('storage/' . $this->image_1);
        }
        return null;
    }

    /**
     * Get the full URL for image_2.
     *
     * @return string|null
     */
    public function getImage2UrlAttribute()
    {
        if ($this->image_2) {
            return asset('storage/' . $this->image_2);
        }
        return null;
    }

    /**
     * Get the full URL for image_3.
     *
     * @return string|null
     */
    public function getImage3UrlAttribute()
    {
        if ($this->image_3) {
            return asset('storage/' . $this->image_3);
        }
        return null;
    }

    /**
     * Get the category tab that owns this tab.
     */
    public function categoryTab()
    {
        return $this->belongsTo(CategoryTab::class);
    }
}
