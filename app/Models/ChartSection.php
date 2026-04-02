<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_title',
        'subtitle',
        'title',
        'description',
        'features',
        'button_text',
        'button_url',
        'main_image',
        'small_image',
        'background_color',
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
     * Get the full URL for the main image.
     *
     * @return string|null
     */
    public function getMainImageUrlAttribute()
    {
        if ($this->main_image) {
            return asset('storage/' . $this->main_image);
        }
        return null;
    }

    /**
     * Get the full URL for the small image.
     *
     * @return string|null
     */
    public function getSmallImageUrlAttribute()
    {
        if ($this->small_image) {
            return asset('storage/' . $this->small_image);
        }
        return null;
    }
}
