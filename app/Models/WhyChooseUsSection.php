<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhyChooseUsSection extends Model
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
        'background_color',
        'card_background_color',
        'image_1',
        'image_2',
        'bad_points',
        'bottom_text',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'bad_points' => 'array',
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
}
