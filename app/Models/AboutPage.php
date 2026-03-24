<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Hero Section
        'hero_background_image',
        'title_part_1',
        'title_part_2',
        'description_1',
        'description_2',
        'hero_image',
        
        // About the Founder Section
        'founder_title',
        'founder_description',
        
        // About our Company Section
        'company_title',
        'company_description',
        'company_image',
        
        // Mission and Vision Section
        'mission_title',
        'vision_title',
        'mission_description',
        'vision_description',
    ];

    /**
     * Get the full URL for the hero background image.
     *
     * @return string|null
     */
    public function getHeroBackgroundImageUrlAttribute()
    {
        if ($this->hero_background_image) {
            return asset('storage/' . $this->hero_background_image);
        }
        return null;
    }

    /**
     * Get the full URL for the hero image.
     *
     * @return string|null
     */
    public function getHeroImageUrlAttribute()
    {
        if ($this->hero_image) {
            return asset('storage/' . $this->hero_image);
        }
        return null;
    }

    /**
     * Get the full URL for the company image.
     *
     * @return string|null
     */
    public function getCompanyImageUrlAttribute()
    {
        if ($this->company_image) {
            return asset('storage/' . $this->company_image);
        }
        return null;
    }
}

