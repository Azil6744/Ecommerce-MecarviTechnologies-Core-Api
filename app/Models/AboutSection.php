<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'main_title',
        'main_description',
        'background_image',
        'tab1_title',
        'tab1_subtitle',
        'tab1_description',
        'tab1_image',
        'tab2_title',
        'tab2_subtitle',
        'tab2_description',
        'tab2_image',
        'about_image_1',
        'about_image_2',
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
     * Get the full URL for tab1 image.
     *
     * @return string|null
     */
    public function getTab1ImageUrlAttribute()
    {
        if ($this->tab1_image) {
            return asset('storage/' . $this->tab1_image);
        }
        return null;
    }

    /**
     * Get the full URL for tab2 image.
     *
     * @return string|null
     */
    public function getTab2ImageUrlAttribute()
    {
        if ($this->tab2_image) {
            return asset('storage/' . $this->tab2_image);
        }
        return null;
    }

    /**
     * Get the full URL for about_image_1.
     *
     * @return string|null
     */
    public function getAboutImage1UrlAttribute()
    {
        if ($this->about_image_1) {
            return asset('storage/' . $this->about_image_1);
        }
        return null;
    }

    /**
     * Get the full URL for about_image_2.
     *
     * @return string|null
     */
    public function getAboutImage2UrlAttribute()
    {
        if ($this->about_image_2) {
            return asset('storage/' . $this->about_image_2);
        }
        return null;
    }
}
