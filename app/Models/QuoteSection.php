<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hero_title',
        'request_quote_title',
        'request_quote_subtitle',
        'description',
        'button_text',
        'title_1',
        'paragraph_1',
        'title_2',
        'paragraph_2',
        'image_1',
        'image_2',
        'background_image',
        'card_1_color',
        'card_2_color',
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
     * Get the full URL for background_image.
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
}
