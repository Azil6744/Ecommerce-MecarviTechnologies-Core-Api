<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hero_background_image',
        'title_part_1',
        'title_part_2',
        'description_1',
        'description_2',
        'hero_image',
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
}

