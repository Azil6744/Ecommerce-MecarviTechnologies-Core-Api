<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurFactsSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_title',
        'small_description',
        'large_number',
        'large_number_image',
        'background_image',
        'background_color',
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
     * Get the full URL for the large number image.
     *
     * @return string|null
     */
    public function getLargeNumberImageUrlAttribute()
    {
        if ($this->large_number_image) {
            return asset('storage/' . $this->large_number_image);
        }
        return null;
    }
}
