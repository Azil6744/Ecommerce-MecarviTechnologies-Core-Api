<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subtitle',
        'main_title',
        'button_text',
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
}
