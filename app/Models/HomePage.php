<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'button_text',
        'button_url',
        'description',
        'background_image',
        'secondary_image',
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
     * Get the full URL for the secondary image.
     *
     * @return string|null
     */
    public function getSecondaryImageUrlAttribute()
    {
        if ($this->secondary_image) {
            return asset('storage/' . $this->secondary_image);
        }
        return null;
    }
}

